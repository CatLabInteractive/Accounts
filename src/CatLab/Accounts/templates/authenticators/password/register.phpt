<?php
	$this->layout ($layout);
	$this->textdomain ('catlab.accounts');
?>

<form method="post" action="<?php echo $action; ?>" role="form">

    <h2><?php echo $this->gettext('Welcome, stranger'); ?></h2>
    <?php echo $this->template ('CatLab/Accounts/blocks/error.phpt'); ?>

	<div class="form-group">
		<label for="email"><?php echo $this->gettext ('Email address'); ?></label>
		<input type="email" class="form-control"  id="email" name="email" value="<?php echo $email; ?>" placeholder="<?php echo $this->gettext ('Email'); ?>" />
	</div>

    <div class="form-group">
        <label for="username"><?php echo $this->gettext ('Username'); ?></label>
        <input type="text" class="form-control"  id="username" name="username" value="<?php echo $username; ?>" placeholder="<?php echo $this->gettext ('Username'); ?>" />
        <p class="help-block"><?php echo $this->gettext ('Your username must be unique and will be visible to other users.'); ?></p>
    </div>

    <div class="form-group">
        <label for="password"><?php echo $this->gettext ('New password'); ?></label>
        <input type="password" class="form-control" id="password" name="password" placeholder="<?php echo $this->gettext ('Password'); ?>" />
    </div>

	<button type="submit" class="btn btn-default"><?php echo $this->gettext ('Register'); ?></button>
</form>