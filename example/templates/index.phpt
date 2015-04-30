<!DOCTYPE html>
<html>
	<head>

		<?php echo $this->combine ('sections/head.phpt'); ?>

	</head>

	<body>

	<div class="container">

		<nav class="navbar navbar-default">

			<div class="container-fluid">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="<?php echo \Neuron\URLBuilder::getURL ('/'); ?>">CatLab Accounts</a>
				</div>

				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
					<?php echo $this->help ('CatLab.Accounts.LoginForm'); ?>
				</div><!-- /.navbar-collapse -->
			</div>

			<?php echo $content; ?>
		</div>

	</body>
</html>