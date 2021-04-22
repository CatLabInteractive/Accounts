<?php
$this->layout ($layout);
$this->textdomain ('catlab.accounts');

/** @var \CatLab\Accounts\Models\User $user */
?>

<h2><?php echo sprintf($this->gettext('Hello, %s'), $name); ?></h2>
<p><?php echo $this->gettext ('Need to change your email address? No problem!'); ?></p>

<div class="panel">
    <div class="panel-body">

        <?php if (isset($error)) { ?>
            <p class="alert alert-danger"><?php echo $error; ?></p>
        <?php } ?>

        <form method="post" action="<?php echo $action; ?>">

            <input type="hidden" name="action" value="change-password" />
            <input type="hidden" name="csfr-token" value="<?php echo $csfr; ?>" />

            <div class="form-group">
                <label for="email"><?php echo $this->gettext('Email address'); ?></label>
                <input type="text" class="form-control" id="email" placeholder="<?php echo $this->gettext('Email address'); ?>" name="email" value="<?php echo htmlentities($user->getEmail()); ?>" autocomplete="email">
            </div>

            <button type="submit" class="btn btn-success"><?php echo $this->gettext ('Change email address'); ?></button>
            <?php if (isset($return_url)) { ?>
                <a href="<?php echo $return_url; ?>" class="btn btn-danger"><?php echo $this->gettext('Back'); ?></a>
            <?php } ?>
        </form>

    </div>
</div>
