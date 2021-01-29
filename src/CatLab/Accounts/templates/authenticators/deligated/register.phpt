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

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="firstName"><?php echo $this->gettext('First name'); ?></label>
                <input type="text" class="form-control" id="firstName" placeholder="<?php echo $this->gettext('First name'); ?>" name="firstName" value="<?php echo htmlentities($firstName); ?>" autocomplete="given-name">
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="lastName"><?php echo $this->gettext('Last name'); ?></label>
                <input type="text" class="form-control" id="lastName" placeholder="<?php echo $this->gettext('Last name'); ?>" name="lastName" value="<?php echo htmlentities($lastName); ?>" autocomplete="family-name">
            </div>
        </div>
    </div>

	<div class="form-group">
		<label for="email"><?php echo $this->gettext ('Email address'); ?></label>
		<input type="email" class="form-control"  id="email" name="email" value="<?php echo $email; ?>" placeholder="<?php echo $this->gettext ('Email'); ?>" />
		<p class="help-block"><?php echo $this->gettext ('Don\'t worry, it\'s our little secret. We won\'t share your email address with anyone.'); ?></p>
	</div>

	<button type="submit" class="btn btn-default"><?php echo $this->gettext ('Register'); ?></button>

	<?php if (isset ($connect)) { ?>
		<p>
			<a href="<?php echo $connect; ?>"><?php echo $this->gettext ('Connect existing account'); ?></a>
		</p>
	<?php } ?>
</form>
