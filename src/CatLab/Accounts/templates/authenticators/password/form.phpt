<form method="post" action="<?php echo $action; ?>" role="form" class="form-signin <?php if (isset ($error)) { ?>has-error<?php } ?>" >

	<h2 class="form-signin-heading"><?php echo $this->gettext ('Please sign in'); ?></h2>

	<?php echo $this->template ('CatLab/Accounts/blocks/error.phpt'); ?>

	<label for="inputEmail" class="sr-only"><?php echo $this->gettext ('Email address'); ?></label>
	<input type="email" class="form-control" id="inputEmail" name="email" value="<?php echo $email; ?>" placeholder="<?php echo $this->gettext ('Enter email'); ?>" required autofocus />

	<label for="inputPassword" class="sr-only"><?php echo $this->gettext ('Password'); ?></label>
	<input type="password" class="form-control" id="inputPassword" name="password" placeholder="<?php echo $this->gettext ('Password'); ?>" required />

	<div class="checkbox">
		<label>
			<input type="checkbox" name="rememberme" id="rememberme"> Remember me
		</label>
	</div>

	<button class="btn btn-lg btn-primary btn-block" type="submit"><?php echo $this->gettext ('Sign in'); ?></button>

</form>