<form method="post" action="<?php echo $action; ?>" role="form" class="form-signin <?php if (isset ($error)) { ?>has-error<?php } ?>" >

    <h2 class="form-signin-heading"><?php echo $this->gettext ('Recover password'); ?></h2>
    <p><?php echo $this->gettext('Please enter your email address.'); ?></p>

    <?php echo $this->template ('CatLab/Accounts/blocks/error.phpt'); ?>

    <label for="inputEmail" class="sr-only"><?php echo $this->gettext ('Email address'); ?></label>
    <input type="email" class="form-control" id="inputEmail" name="email" value="<?php echo $email; ?>" placeholder="<?php echo $this->gettext ('Enter email'); ?>" required autofocus />

    <button class="btn btn-lg btn-primary btn-block" type="submit"><?php echo $this->gettext ('Recover password'); ?></button>

    <p class="login">
        <a href="<?php echo $login; ?>"><?php echo $this->gettext ('Go back'); ?></a>
    </p>

</form>