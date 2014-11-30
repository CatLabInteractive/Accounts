<?php
    $this->layout ('index.phpt');
    $this->textdomain ('catlab.accounts');
?>

<form method="post" action="<?php echo $action; ?>" role="form">

    <div class="form-group">
        <label for="email"><?php echo $this->gettext ('Email address'); ?></label>
        <input type="text" class="form-control" id="email" name="email" value="<?php echo $email; ?>" placeholder="<?php echo $this->gettext ('Enter email'); ?>" />
    </div>

    <div class="form-group">
        <label for="password"><?php echo $this->gettext ('Password'); ?></label>
        <input type="password" class="form-control" id="password" name="password" placeholder="<?php echo $this->gettext ('Password'); ?>" />
    </div>

    <div class="checkbox">
        <label>
            <input type="checkbox" name="rememberme" id="rememberme"> Remember me
        </label>
    </div>

    <button type="submit"><?php echo $this->gettext ('Login'); ?></button>
</form>