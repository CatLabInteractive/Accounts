<?php
	$this->layout ($layout);
	$this->textdomain ('catlab.accounts');

	/** @var \CatLab\Accounts\Models\User $user */
?>

<h2><?php echo sprintf($this->gettext('Welcome, %s'), $name); ?></h2>
<p><?php echo $this->gettext ('Almost there, we just need to confirm your email address.'); ?></p>

<div class="panel">
    <div class="panel-body">
        <p>
            <?php echo sprintf($this->gettext ('We have sent a confirmation email to %s.'), $user->getEmail()); ?><br />
            <?php echo $this->gettext ('Please click the link in the email in order to verify your account.'); ?><br />
            <?php echo $this->gettext ('Made a mistake?'); ?>
            <a href="<?php echo $changeAddress_url; ?>"><?php echo $this->gettext ('Change your email address'); ?></a>
        </p>

        <?php if ($canResend) { ?>
            <p>
                <?php echo $this->gettext ('Haven\'t received our email?'); ?><br />
                <a href="<?php echo $resend_url; ?>"><?php echo $this->gettext ('Resend verification email'); ?></a>
            </p>
        <?php } ?>
    </div>
</div>

<?php if ($pollAction) { ?>
    <script type="text/javascript">
        function verifiedPollAction() {
            $.ajax('<?php echo $pollAction; ?>')
                .done(function(content) {
                    if (typeof(content.redirect) !== 'undefined') {
                        window.location = content.redirect;
                    } else if (typeof(content.wait) !== 'undefined') {
                        setTimeout(function() {
                            verifiedPollAction();
                        }, content.wait);
                    }
                })
                .fail(function() {
                    setTimeout(function() {
                        verifiedPollAction();
                    }, 5000);
                });
        }
        setTimeout(function() {
            verifiedPollAction();
        }, 5000);
    </script>
<?php } ?>
