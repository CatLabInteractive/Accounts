<?php
    $this->layout ($layout);
    $this->textdomain ('catlab.accounts');
?>

<?php foreach ($authenticators as $authenticator) { ?>

    <?php echo $authenticator->getForm (); ?>

<?php } ?>

<?php if (isset ($cancel)) { ?>
    <p style="text-align: center;"><a href="<?php echo $cancel; ?>"><?php echo $this->gettext ('I don\'t want to login right now'); ?></a></p>
<?php } ?>