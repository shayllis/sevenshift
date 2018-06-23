<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;

use Cake\I18n\Time;
use Cake\Http\Client;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link https://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{
	public function home()
	{

		$http = new Client();

		// Get remote data
		$locations = $http->get('https://shiftstestapi.firebaseio.com/locations.json')->json;
		$employees = $http->get('https://shiftstestapi.firebaseio.com/users.json')->json;
		$timePunches = $http->get('https://shiftstestapi.firebaseio.com/timePunches.json')->json;

		// DEMO DATA
		// $locations = [
		// 	"25753" => [
		// 		"address" => "7shifts Brazil",
		// 		"city" => "S\u00e3o Paulo",
		// 		"country" => "US",
		// 		"created" => "2017-07-06 22:40:21",
		// 		"id" => 25753,
		// 		"labourSettings" => [
		// 			"autoBreak" => false,
		// 			"autoBreakRules" => [
		// 				[
		// 					"breakLength" => 30,
		// 					"threshold" => 480
		// 				],
		// 				[
		// 					"breakLength" => 30,
		// 					"threshold" => 480
		// 				]
		// 			],
		// 			"dailyOvertimeMultiplier" => 1.5,
		// 			"dailyOvertimeThreshold" => 480,
		// 			"overtime" => true,
		// 			"weeklyOvertimeMultiplier" => 2,
		// 			"weeklyOvertimeThreshold" => 2400
		// 		],
		// 		"lat" => 34.0522342,
		// 		"lng" => -118.2436849,
		// 		"modified" => "2018-06-08 17:57:23",
		// 		"state" => "CA",
		// 		"timezone" => "America\/Sao_Paulo"
		// 	]
		// ];
		//
		// $timePunches = [
		// 	"4046830" => [
		// 		"clockedIn" => "2017-10-11 21:11:00",
		// 		"clockedOut" => "2017-10-12 05:45:00",
		// 		"created" => "2017-10-11 21:11:38",
		// 		"hourlyWage" => 10,
		// 		"id" => 4046830,
		// 		"locationId" => 25753,
		// 		"modified" => "2017-10-12 05:45:49",
		// 		"userId" => 517135
		// 	],
		// ];
		//
		// $employees = [
		// 	"25753" => [
		// 		"517135" => [
		// 			"active" => true,
		// 			"created" => "2017-07-06 22:40:21",
		// 			"email" => "abf@7shiftstest.com",
		// 			"firstName" => "Jeff",
		// 			"hourlyWage" => 0,
		// 			"id" => 517135,
		// 			"lastName" => "Thompson ",
		// 			"locationId" => 25753,
		// 			"modified" => "2018-06-16 20:47:28",
		// 			"photo" => "https:\/\/randomuser.me\/api\/portraits\/men\/72.jpg",
		// 			"userType" => 2
		// 		]
		// 	]
		// ];

		// Nasted Data
		$employeesList = [];
		// Initialize counters;
		foreach ($employees as $locationId => &$list) {
			array_walk($list, function ($user) use($locationId, &$employeesList){
				if (!isset($employeesList[$user['id']])) {
					$employeesList[$user['id']] = $user;
					$employeesList[$user['id']]['locations'] = [];
				}
				$employeesList[$user['id']]['locations'][$locationId] = [
					'locationId' => $locationId,
					'totalWorkedTime' => 0,
					'dayOverTime' => 0,
					'weekOverTime' => 0,
					'weeks' => []
				];
			});
		}

		// Free Memory
		unset($employees);

		foreach ($timePunches as $time) {
			// Read dates
			$time['clockedIn'] = new Time($time['clockedIn']);
			$time['clockedOut'] = new Time($time['clockedOut']);

			$employee = &$employeesList[$time['userId']]['locations'][$time['locationId']];

			$workedTime =
				$time['clockedOut']->diff($time['clockedIn']);

			$employee['totalWorkedTime'] +=
				$workedTime =
					$workedTime->h * 60
					+ $workedTime->i;

			$location = &$locations[$time['locationId']];

			$week = $time['clockedIn']->format('Y-W');
			$employee['weeks'][$week] = ($employee[$week] ?? 0) + $workedTime;

			// Check day worked time
			if ($workedTime > $location['labourSettings']['dailyOvertimeThreshold']) {
				$employee['dayOverTime'] += $workedTime - $location['labourSettings']['dailyOvertimeThreshold'];
			}
		}

		// Calculates Week Over Time and Paid Hours
		foreach ($employeesList as &$employee) {
			$employee['totalWorkedTime'] = 0;
			$employee['paidTime'] = 0;

			foreach ($employee['locations'] as &$details) {
				$location = &$locations[$details['locationId']];

				// Calculate week over time rule
				$details['weekOverTime'] = 0;
				array_walk(
					$details['weeks'],
					function ($w) use(&$location, &$details){
						if ($w > $location['labourSettings']['weeklyOvertimeThreshold']){
							$details['weekOverTime'] += $w - $location['labourSettings']['weeklyOvertimeThreshold'];
						}
					}
				);

				$details['paidTime'] =
					$details['dayOverTime'] * $location['labourSettings']['dailyOvertimeMultiplier']
					+ (
						$details['weekOverTime'] * $location['labourSettings']['weeklyOvertimeMultiplier']
					)
					+ $details['totalWorkedTime'] - $details['weekOverTime'] - $details['dayOverTime'];

				$employee['totalWorkedTime'] += $details['totalWorkedTime'];
				$employee['paidTime'] += $details['paidTime'];
			}

		}
		$this->set([
			'locations' => $locations,
			'employees' => $employeesList
		]);
	}
}
