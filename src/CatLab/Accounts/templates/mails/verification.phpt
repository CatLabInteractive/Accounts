<?php $this->layout ('CatLab/Accounts/mails/layout.phpt'); ?>

<p>Well hello <?php echo $user->getUsername (); ?></p>

<p>
    <a href="<?php echo $verify_url; ?>">Verify your account</a>
</p>