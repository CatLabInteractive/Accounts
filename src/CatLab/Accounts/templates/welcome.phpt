<?php
    $this->layout ($layout);
    $this->textdomain ('catlab.accounts');
?>

<h2><?php echo sprintf($this->gettext('Welcome, %s'), $name); ?></h2>
<p><?php echo $this->gettext('We\'re sending you back to the application.'); ?></p>

<script>
    dataLayer = [<?php echo json_encode($tracker); ?>];

    var redirect_url = '<?php echo $redirect_url; ?>';
    setTimeout(function() {
        window.location = redirect_url;
    }, 2000);
</script>
