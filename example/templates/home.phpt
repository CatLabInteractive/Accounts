<?php $this->layout ('index.phpt'); ?>

		<p>Welcome to the world of tomorrow!</p>

		<ul>
			<li>
				<a href="<?php echo \Neuron\URLBuilder::getURL ('account/login'); ?>">Login</a>
			</li>

			<li>
				<a href="<?php echo \Neuron\URLBuilder::getURL ('account/register'); ?>">Register</a>
			</li>

			<li>
				<a href="<?php echo \Neuron\URLBuilder::getURL ('account/logout'); ?>">Logout</a>
			</li>
		</ul>

		<div>
			<?php echo $this->help ('CatLab.Accounts.LoginForm', 'smallForm'); ?>
		</div>