<?php $this->layout ('CatLab/Accounts/mails/layout.phpt'); ?>

<p><?php echo sprintf($this->gettext('Dear %s,'), $user->getUsername ()); ?></p>

<p><?php echo $this->gettext('We have received a password recovery request for this email address.'); ?></p>
<p><?php echo $this->gettext('If you have submitted this request, please click the link below:'); ?></p>

<p>
    <a href="<?php echo $recovery_url; ?>"><?php echo $this->gettext('Recover your password'); ?></a>
</p>

<p><?php echo $this->gettext('If you did not submit this request, please ignore this email.'); ?></p>

<p><?php echo $this->gettext('Have fun!'); ?></p>