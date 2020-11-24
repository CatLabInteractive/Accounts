<?php $this->layout ('CatLab/Accounts/mails/layout.phpt'); ?>

<p><?php echo sprintf ($this->gettext ('Hello %s'), $user->getUsername ()); ?></p>

<p>
    <?php echo $this->gettext('We are writing you this email to inform you that this email address was used when creating an account on our platform.'); ?>
</p>

<p>
    <?php echo $this->gettext('Thank you for subscribing to our platform.'); ?>
</p>

<p>
    <?php echo $this->gettext('Many greetings'); ?>,<br />
    <?php echo \Neuron\Config::get('app.organisation.name'); ?><br />
    <?php echo \Neuron\Config::get('app.organisation.email'); ?>
</p>
