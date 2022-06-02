<p class="authenticator inline navbar-text navbar-left <?php echo $authenticator->getToken (); ?>">
	<a href="<?php echo $url; ?>">
		<span>
			<?php echo sprintf ($this->gettext ('Sign in with %s'), $authenticator->getName ()); ?>
		</span>
	</a>
</p>
