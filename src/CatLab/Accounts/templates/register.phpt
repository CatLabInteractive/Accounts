<?php
    $this->layout ('index.phpt');
    $this->textdomain ('catlab.accounts');
?>

<form method="post" action="<?php echo $action; ?>">
    <fieldset>

        <legend><?php echo $this->gettext ('Register'); ?></legend>

        <ol>
            <li>
                <label for="email"><?php echo $this->gettext ('Email'); ?></label>
                <input type="text" id="email" name="email" value="<?php echo $email; ?>" placeholder="<?php echo $this->gettext ('Email'); ?>" />
            </li>

            <li>
                <label for="password"><?php echo $this->gettext ('Password'); ?></label>
                <input type="password" id="password" name="password" placeholder="<?php echo $this->gettext ('Password'); ?>" />
            </li>

            <li>
                <button type="submit"><?php echo $this->gettext ('Register'); ?></button>
            </li>
        </ol>

    </fieldset>
</form>