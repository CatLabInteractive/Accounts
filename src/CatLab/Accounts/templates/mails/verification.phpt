<?php
$this->layout ('CatLab/Accounts/mails/layout.phpt');
$this->textdomain ('catlab.accounts');
?>

<p><?php echo sprintf ($this->gettext ('Hello %s'), $user->getUsername ()); ?></p>

<p>
    <?php echo $this->gettext('Thank you for subscribing to our platform.'); ?>
</p>

<p>
    <?php echo $this->gettext('To complete email verification, please click the link below or copy/paste it in your browser:'); ?>
</p>

<p>
    <a href="<?php echo $verify_url; ?>"><?php echo $verify_url; ?></a>
</p>

<p><?php echo $this->gettext('If you did not create an account using this address, please ignore this email.'); ?></p>

<p>
    <?php echo $this->gettext('Many greetings'); ?>,<br />
    <?php echo \Neuron\Config::get('app.organisation.name'); ?><br />
    <?php echo \Neuron\Config::get('app.organisation.email'); ?>
</p>
