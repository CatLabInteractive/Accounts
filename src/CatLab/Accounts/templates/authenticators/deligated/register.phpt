<?php
	$this->layout ($layout);
	$this->textdomain ('catlab.accounts');
?>

<form method="post" action="<?php echo $action; ?>" role="form">

	<?php if (isset ($name)) { ?>
		<h2><?php echo sprintf ($this->gettext ('Welcome, %s'), $name); ?></h2>
	<?php } else { ?>
		<h2><?php echo $this->gettext ('Welcome!'); ?></h2>
	<?php } ?>

	<h3><?php echo $this->gettext ('Almost there!'); ?></h3>
	<p><?php echo $this->gettext ('We just need a bit more information in order to get started...'); ?></p>

	<?php echo $this->template ('CatLab/Accounts/blocks/error.phpt'); ?>

	<div class="form-group">
		<label for="email"><?php echo $this->gettext ('Email address'); ?></label>
		<input type="email" class="form-control"  id="email" name="email" value="<?php echo $email; ?>" placeholder="<?php echo $this->gettext ('Email'); ?>" />
		<p class="help-block"><?php echo $this->gettext ('Don\'t worry, it\'s our little secret. We won\'t share your email address with anyone.'); ?></p>
	</div>

	<div class="form-group">
		<label for="username"><?php echo $this->gettext ('Username'); ?></label>
		<input type="text" class="form-control"  id="username" name="username" value="<?php echo $username; ?>" placeholder="<?php echo $this->gettext ('Username'); ?>" />
		<p class="help-block"><?php echo $this->gettext ('Your username must be unique and will be visible to other users.'); ?></p>
	</div>

	<button type="submit" class="btn btn-default"><?php echo $this->gettext ('Register'); ?></button>
</form>