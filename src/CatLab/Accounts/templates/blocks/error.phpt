<?php if (isset ($error)) { ?>
	<div class="alert alert-danger" role="alert">

		<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
		<span class="sr-only">Error:</span>

		<?php if ($error === 'USER_NOT_FOUND') { ?>

			<?php echo $this->gettext ('We don\'t know anyone with that email address.'); ?>

		<?php } else if ($error === 'PASSWORD_INCORRECT') { ?>

			<?php echo $this->gettext ('The password you have provided is incorrect.'); ?>

		<?php } else if ($error === 'EMAIL_DUPLICATE') { ?>

			<?php echo $this->gettext ('This email address is already registered in our database.'); ?>

			<?php if (isset ($deligated) && $deligated) { ?>
				<?php echo sprintf (
					$this->gettext (
						'If you already have an account here, you can %s.'),
					'<a href="' . $connect . '">' . $this->gettext ('link it up') . '</a>'
				); ?>
			<?php } ?>

		<?php } else if ($error === 'USERNAME_DUPLICATE') { ?>

			<?php echo $this->gettext ('This username is already in use.'); ?>

			<?php if (isset ($deligated) && $deligated) { ?>
				<?php echo sprintf (
					$this->gettext (
						'If you already have an account here, you can %s.'),
					'<a href="' . $connect . '">' . $this->gettext ('link it up') . '</a>'
				); ?>
			<?php } ?>

		<?php } else { ?>

			<?php echo $error; ?>

		<?php } ?>
	</div>
<?php } ?>