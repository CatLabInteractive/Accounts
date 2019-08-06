<?php
    $this->layout ($layout);
    $this->textdomain ('catlab.accounts'); ?>

<div class="password-authenticator">
	<?php foreach ($authenticators as $authenticator) { ?>
		<?php if ($authenticator instanceof \CatLab\Accounts\Authenticators\Password) { ?>
			<?php echo $authenticator->getForm (); ?>
		<?php } ?>
	<?php } ?>
</div>

<?php if (isset ($cancel)) { ?>
    <p style="text-align: center;"><a href="<?php echo $cancel; ?>"><?php echo $this->gettext ('I don\'t want to login right now'); ?></a></p>
<?php } ?>

<div class="other-authenticators">
    <p class="connect-with">Connect with:<br /></p>
    <div class="media-connections">
    <?php foreach ($authenticators as $authenticator) { ?>
        <?php if (! ($authenticator instanceof \CatLab\Accounts\Authenticators\Password)) { ?>
            <?php echo $authenticator->getInlineForm (); ?>
        <?php } ?>
    <?php } ?>
    </div>
</div>