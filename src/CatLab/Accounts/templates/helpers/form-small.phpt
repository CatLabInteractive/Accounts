<?php $this->textdomain ('catlab.accounts'); ?>

<div class="authentication inline navbar-right">
	<?php foreach ($authenticators as $authenticator) { ?>

		<?php echo $authenticator->getInlineForm (); ?>

	<?php } ?>
</div>