<?php $this->layout ('index.phpt'); ?>

<form method="post" action="<?php echo $action; ?>">
    <fieldset>

        <legend>Register</legend>

        <ol>
            <li>
                <label for="email">Email</label>
                <input type="text" id="email" name="email" value="<?php $email; ?>" placeholder="Email" />
            </li>

            <li>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Password" />
            </li>

            <li>
                <button type="submit">Register</button>
            </li>
        </ol>

    </fieldset>
</form>