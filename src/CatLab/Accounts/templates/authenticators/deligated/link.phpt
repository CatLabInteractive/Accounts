<?php
	$this->layout ($layout);
	$this->textdomain ('catlab.accounts');
?>

<form method="post" action="<?php echo $action; ?>" role="form" class="form-signin <?php if (isset ($error)) { ?>has-error<?php } ?>" >

	<?php if (isset ($name)) { ?>
		<h2><?php echo sprintf ($this->gettext ('Welcome, %s'), $name); ?></h2>
	<?php } else { ?>
		<h2><?php echo $this->gettext ('Welcome!'); ?></h2>
	<?php } ?>

	<h3><?php echo $this->gettext ('Link account'); ?></h3>
	<p><?php echo $this->gettext ('Please supply your email and password in order to link up an existing account.'); ?></p>

	<?php echo $this->template ('CatLab/Accounts/blocks/error.phpt'); ?>

	<label for="inputEmail" class="sr-only"><?php echo $this->gettext ('Email address'); ?></label>
	<input type="email" class="form-control" id="inputEmail" name="email" value="<?php echo $email; ?>" placeholder="<?php echo $this->gettext ('Enter email'); ?>" required autofocus />

	<label for="inputPassword" class="sr-only"><?php echo $this->gettext ('Password'); ?></label>
	<input type="password" class="form-control" id="inputPassword" name="password" placeholder="<?php echo $this->gettext ('Password'); ?>" required />

	<button class="btn btn-lg btn-primary btn-block" type="submit"><?php echo $this->gettext ('Sign in'); ?></button>

	<p>
		<a href="<?php echo $return; ?>">
			<?php echo $this->gettext ('Never mind, I don\'t have an account anyway'); ?>
		</a>
	</p>

</form>