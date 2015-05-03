<?php
	$this->layout ('index.phpt');
	$this->textdomain ('example');
?>

	<h2>Yip.</h2>
	<p><?php echo $this->gettext ('Welcome to the world of tomorrow!'); ?></p>


	<p>
		<a href="<?php echo \Neuron\URLBuilder::getURL ('thirdparty'); ?>">Third party accounts</a>
	</p>