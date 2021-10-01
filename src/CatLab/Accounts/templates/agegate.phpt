<?php
$this->layout ($layout);
$this->textdomain ('catlab.accounts');
?>

<h2><?php echo $this->gettext('Just a few more years'); ?></h2>
<div class="alert alert-danger">
    <p><?php echo sprintf($this->gettext('We\'re terribly sorry, but our platform was developed for people of age %s and up.'), $module->getMinimumAge()); ?></p>
    <p><?php echo $this->gettext('Perhaps you can join in a few years?'); ?></p>
</div>

<p><a href="<?php echo $return; ?>" class="btn btn-primary"><?php echo $this->gettext('Return'); ?></a></p>
