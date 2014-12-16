<?php $this->textdomain ('catlab.accounts'); ?>

<p>
	<?php echo sprintf ($this->gettext ('Welcome, %s.'), $user->getUsername ()); ?>
	<a href="<?php echo $logout; ?>"><?php echo $this->getText ('logout'); ?></a>
</p>