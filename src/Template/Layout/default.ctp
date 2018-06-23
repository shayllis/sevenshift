<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $this->fetch('title') ?>
    </title>
    <?=
		$this->Html->meta('icon')

    .$this->fetch('meta')
		.$this->Html->css([
			'main'
		])
		?>
</head>
<body>
    <?= $this->Flash->render() ?>
    <div class="container">
        <?= $this->fetch('content') ?>
    </div>
		<?= $this->Html->script([
			'/plugins/jquery/jquery-3.3.1.min.js',
			'/plugins/bootstrap/assets/javascripts/bootstrap.min.js',
			'/plugins/dataTable/js/dataTable.js'
			]) ?>
</body>
</html>
