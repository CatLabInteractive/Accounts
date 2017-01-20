<form method="post" role="form" class="form-signin <?php if (isset ($error)) { ?>has-error<?php } ?>" >

    <h2 class="form-signin-heading"><?php echo $this->gettext ('Recover password'); ?></h2>

    <p><?php echo $this->gettext('If your email address matches the one you have used to register, we will now be sending an email with a reactivation code.'); ?></p>
    <p><?php echo $this->gettext('Please check your mailbox and click the link we have sent to you.'); ?></p>

    <p class="login">
        <a href="<?php echo $login; ?>"><?php echo $this->gettext ('Go back'); ?></a>
    </p>

</form>