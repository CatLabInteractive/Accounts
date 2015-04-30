<?php $this->textdomain ('catlab.accounts'); ?>

<div class="authentication inline">
	<?php foreach ($authenticators as $authenticator) { ?>

		<?php echo $authenticator->getInlineForm (); ?>

	<?php } ?>
</div>