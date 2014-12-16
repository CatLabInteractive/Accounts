<!DOCTYPE html>
<html>
	<head>

		<?php echo $this->combine ('sections/head.phpt'); ?>
		<?php echo $this->css ('/assets/css/signin.css'); ?>

	</head>

	<body>

		<div class="container">
			<?php echo $content; ?>
		</div>

		<div id="debug"><?php var_dump (\Neuron\Application::getInstance()->getRouter ()->getRequest ()->getSession ()->all ()); ?></div>

	</body>
</html>