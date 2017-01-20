<?php

use CatLab\Accounts\Enums\Errors;

?>

<?php if (isset ($error)) { ?>
	<div class="alert alert-danger" role="alert">

		<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
		<span class="sr-only">Error:</span>

		<?php if ($error === Errors::USER_NOT_FOUND) { ?>

			<?php echo $this->gettext ('We don\'t know anyone with that email address.'); ?>

		<?php } else if ($error === Errors::PASSWORD_INCORRECT) { ?>

			<?php echo $this->gettext ('The password you have provided is incorrect.'); ?>

		<?php } else if ($error === Errors::EMAIL_DUPLICATE) { ?>

			<?php echo $this->gettext ('This email address is already registered in our database.'); ?>

			<?php if (isset ($deligated) && $deligated) { ?>
				<?php echo sprintf (
					$this->gettext (
						'If you already have an account here, you can %s.'),
					'<a href="' . $connect . '">' . $this->gettext ('link it up') . '</a>'
				); ?>
			<?php } ?>

		<?php } else if ($error === Errors::USERNAME_DUPLICATE) { ?>

			<?php echo $this->gettext ('This username is already in use.'); ?>

			<?php if (isset ($deligated) && $deligated) { ?>
				<?php echo sprintf (
					$this->gettext (
						'If you already have an account here, you can %s.'),
					'<a href="' . $connect . '">' . $this->gettext ('link it up') . '</a>'
				); ?>
			<?php } ?>

        <?php } else if ($error === Errors::CONFIRM_PASSWORD_INVALID) { ?>

            <?php echo $this->gettext ('Your passwords do not match.'); ?>

        <?php } else if ($error === Errors::PASSWORD_INVALID) { ?>

            <?php echo $this->gettext ('This password does not match our security requirements.'); ?>
            <?php echo $this->gettext ('Please choose a different password.'); ?>

        <?php } else { ?>

			<?php echo $error; ?>

		<?php } ?>
	</div>
<?php } ?>