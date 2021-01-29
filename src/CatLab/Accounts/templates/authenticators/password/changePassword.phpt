<form method="post" action="<?php echo $action; ?>" role="form" class="form-signin <?php if (isset ($error)) { ?>has-error<?php } ?>" >

    <h2><?php echo $this->gettext('Change password'); ?></h2>
    <?php echo $this->template ('CatLab/Accounts/blocks/error.phpt'); ?>

    <p>
        <?php echo sprintf($this->gettext('Almost there, %s.'), $user->getDisplayName()); ?>
        <?php echo $this->gettext('Please choose a new password.'); ?>
    </p>

    <div class="form-group">
        <label for="password"><?php echo $this->gettext ('New password'); ?></label>
        <input type="password" class="form-control" id="password" name="password" placeholder="<?php echo $this->gettext ('Password'); ?>" />
    </div>

    <div class="form-group">
        <label for="password_confirmation"><?php echo $this->gettext ('Repeat password'); ?></label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="<?php echo $this->gettext ('Repeat password'); ?>" />
    </div>

    <button type="submit" class="btn btn-default"><?php echo $this->gettext ('Change password'); ?></button>
</form>
