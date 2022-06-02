<form method="post" action="<?php echo $action; ?>" role="form" class="form-signin <?php if (isset ($error)) { ?>has-error<?php } ?>" >

	<h2 class="form-signin-heading"><?php echo $this->gettext ('Please sign in'); ?></h2>
    <!-- <?php echo $this->gettext ('Login'); ?> -->
    <p><?php echo $this->gettext ('This part of our application is only available for authenticated users.'); ?></p>

    <?php if (isset($otherAuthenticators)) { ?>
        <?php echo $this->gettext ('Connect with:'); ?>
        <?php foreach ($otherAuthenticators as $authenticator) { ?>
            <?php echo $authenticator->getInlineForm (); ?>
        <?php } ?>
    <?php } ?>

    <h3 class="password-registration"><?php echo $this->gettext('Password registration'); ?></h3>
    <p><?php echo $this->gettext ('Or use our password based authentication:'); ?></p>

	<?php echo $this->template ('CatLab/Accounts/blocks/error.phpt'); ?>

	<label for="inputEmail" class="sr-only"><?php echo $this->gettext ('Email address'); ?></label>
	<input type="email" class="form-control" id="inputEmail" name="email" autocomplete="username" value="<?php echo $email; ?>" placeholder="<?php echo $this->gettext ('Enter email'); ?>" required autofocus />

	<label for="inputPassword" class="sr-only"><?php echo $this->gettext ('Password'); ?></label>
	<input type="password" class="form-control" id="inputPassword" autocomplete="current-password" name="password" placeholder="<?php echo $this->gettext ('Password'); ?>" required />

    <input type="hidden" name="csfr-token" value="<?php echo $csfr; ?>" />

	<button class="btn btn-lg btn-primary btn-block" type="submit"><?php echo $this->gettext ('Sign in'); ?></button>

    <ul class="login-actions">
        <li>
            <a href="<?php echo $lostPassword; ?>"><?php echo $this->gettext('Forgot password?'); ?></a>
        </li>

        <li>
            <?php echo $this->gettext('Not registered?'); ?> <a href="<?php echo $register; ?>"><?php echo $this->gettext('Sign up now.'); ?></a>
        </li>
    </ul>

</form>
