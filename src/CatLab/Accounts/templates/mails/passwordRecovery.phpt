<?php $this->layout ('CatLab/Accounts/mails/layout.phpt'); ?>

<p>Well hello <?php echo $user->getUsername (); ?></p>

<p>
    <a href="<?php echo $recovery_url; ?>"><?php echo $this->gettext('Recover your password'); ?></a>
</p>