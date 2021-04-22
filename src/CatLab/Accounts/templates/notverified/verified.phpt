<?php
	$this->layout ($layout);
	$this->textdomain ('catlab.accounts');
?>

<h2><?php echo $this->gettext('Email address verified'); ?></h2>
<p>
    <?php echo $this->gettext ('Thanks! Your email address is now verified.'); ?><br />
    <?php echo $this->gettext ('You may now close this window.'); ?>
</p>
