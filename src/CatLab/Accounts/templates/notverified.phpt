<?php
	$this->layout ($layout);
	$this->textdomain ('catlab.accounts');
?>

<p><?php echo $this->gettext ('Almost there. You just need to verify your account in order to get started.'); ?></p>

<p><?php echo $this->gettext ('We have sent a confirmation email to your email address. Please click the link in the email in order to verify your account.'); ?></p>

<p>
    <?php echo $this->gettext ('Haven\'t reveived it yet?'); ?>
    <a href="<?php echo $resend_url; ?>"><?php echo $this->gettext ('Resend verification email'); ?></a>
</p>