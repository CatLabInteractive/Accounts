<?php
	$this->layout ($layout);
	$this->textdomain ('catlab.accounts');
?>

<form method="post" action="<?php echo $action; ?>" role="form" id="registerForm">

    <h2><?php echo $this->gettext('Welcome, stranger'); ?></h2>
    <?php echo $this->template ('CatLab/Accounts/blocks/error.phpt'); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="firstName"><?php echo $this->gettext('First name'); ?></label>
                <input type="text" class="form-control" id="firstName" placeholder="<?php echo $this->gettext('First name'); ?>" name="firstName" value="<?php echo htmlentities($billingDetails->firstName); ?>" autocomplete="given-name">
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="lastName"><?php echo $this->gettext('Last name'); ?></label>
                <input type="text" class="form-control" id="lastName" placeholder="<?php echo $this->gettext('Last name'); ?>" name="lastName" value="<?php echo htmlentities($billingDetails->lastName); ?>" autocomplete="family-name">
            </div>
        </div>
    </div>

	<div class="form-group">
		<label for="email"><?php echo $this->gettext ('Email address'); ?></label>
		<input type="email" class="form-control"  id="email" name="email" value="<?php echo $email; ?>" placeholder="<?php echo $this->gettext ('Email'); ?>" autocomplete="email" />
	</div>

    <div class="form-group">
        <label for="password"><?php echo $this->gettext ('New password'); ?></label>
        <input type="password" class="form-control" id="password" name="password" placeholder="<?php echo $this->gettext ('Password'); ?>" autocomplete="new-password" />
        <p class="help-block"><?php echo $this->gettext ('Minimum 8 characters.'); ?></p>
    </div>

    <?php if (isset($otherAuthenticators)) { ?>
        <div class="other-authenticators">
            <p class="connect-with"><?php echo $this->gettext ('Connect with:'); ?><br /></p>
            <div class="media-connections">
            <?php foreach ($otherAuthenticators as $authenticator) { ?>
                <?php if (! ($authenticator instanceof \CatLab\Accounts\Authenticators\Password)) { ?>
                    <?php echo $authenticator->getInlineForm (); ?>
                <?php } ?>
            <?php } ?>
            </div>
        </div>
    <?php } ?>

    <input type="hidden" class="hidden" name="token" value="<?php echo $token; ?>" />

    <?php if (isset($recaptchaClientKey)) { ?>
        <script src='https://www.google.com/recaptcha/api.js'></script>
        <script type="text/javascript">
            function submitRegistration() {
                document.getElementById('registerForm').submit();
            }
        </script>
        <button
                class="g-recaptcha btn btn-default"
                data-sitekey="<?php echo $recaptchaClientKey; ?>"
                data-callback="submitRegistration"
        ><?php echo $this->gettext ('Register'); ?></button>
    <?php } else { ?>
        <button type="submit" class="g-recaptcha btn btn-default"><?php echo $this->gettext ('Register'); ?></button>
    <?php } ?>

</form>
