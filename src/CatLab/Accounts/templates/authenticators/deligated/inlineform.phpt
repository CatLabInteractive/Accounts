<p class="authenticator inline navbar-text navbar-left <?php echo $authenticator->getToken (); ?>'">
	<a href="<?php echo $url; ?>">
		<?php echo sprintf ($this->gettext ('Connect with %s'), $authenticator->getName ()); ?>
	</a>
</p>