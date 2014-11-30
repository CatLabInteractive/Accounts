<?php if (isset ($error)) { ?>
	<div class="alert alert-danger" role="alert">

		<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
		<span class="sr-only">Error:</span>

		<?php if ($error === 'USER_NOT_FOUND') { ?>

			<?php echo $this->gettext ('We don\'t know anyone with that email address.'); ?>

		<?php } else if ($error === 'PASSWORD_INCORRECT') { ?>

			<?php echo $this->gettext ('The password you have provided is incorrect.'); ?>

		<?php } ?>
	</div>
<?php } ?>