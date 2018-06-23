<?php
	$getFormatedTime = function($m) {
		return floor($m / 60).'h '.($m % 60).'m';
	}

?>
<div class="panel">
  <div class="panel-heading">
    <div class="panel-title">
      Employees List
    </div>
  </div>
	<div class="panel-body">
		<div class="table-responsive table-primary">
      <table class="table table-striped table-bordered" data-title="<?= __d('Admin.ads_list', 'API list') ?>">
        <thead>
          <tr>
            <th class="col-sm-7">Name</th>
            <th class="col-sm-2">Worked Time</th>
            <th class="col-sm-2">Paid Time</th>
						<th class="col-sm-1"></th>
          </tr>
        </thead>
					<?=
						join(
							'',
							array_map(
								function($employee) use (&$locations, $getFormatedTime){
									return $this->Html->tag(
										'tr',
										[

											"<td>{$employee['firstName']} {$employee['lastName']}</td>",
											'<td>'.$getFormatedTime($employee['totalWorkedTime']).'</td>',
											'<td>'.$getFormatedTime($employee['paidTime']).'</td>',
											"<td><button  data-toggle=\"collapse\" data-target=\"#details-{$employee['id']}\" class=\"btn btn-primary fa fa-plus\"></button></td>"
										]
									)
									.$this->Html->tag(
										'tr',
										$this->Html->tag(
											'td',
											[
												$this->Html->tag(
													'table',
													[
														$this->Html->tag(
														'tr',
														[
															$this->Html->tag('td', 'Location'),
															$this->Html->tag('td', 'Worked Time'),
															$this->Html->tag('td', 'Paid Time', ['class' => 'col-sm-2'])
														]),
														join(
															'',
															array_map(
																function ($details) use(&$locations, $getFormatedTime) {
																	return $this->Html->tag(
																		'tr',
																		[
																			$this->Html->tag('td', $locations[$details['locationId']]['address']),
																			$this->Html->tag('td', $getFormatedTime($details['totalWorkedTime'])),
																			$this->Html->tag('td', $getFormatedTime($details['paidTime']))
																		]
																	);
																},
																$employee['locations']
															)
														)
													],
													[
														'class' => 'table'
													]
												)
											],
											[
												'colspan' => 4,
												'class' => 'no-padding'
											]
										),
										[
											'id' => "details-{$employee['id']}",
											'class' => 'collapse details'
										]
									);
								},
								$employees
							)
						)
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>
