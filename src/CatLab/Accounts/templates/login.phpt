<?php
    $this->layout ($layout);
    $this->textdomain ('catlab.accounts');
?>

<?php foreach ($authenticators as $authenticator) { ?>

    <?php echo $authenticator->getForm (); ?>

<?php } ?>