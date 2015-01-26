<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 29/11/14
 * Time: 11:46
 */
namespace CatLab\Accounts\Authenticators;

use CatLab\Accounts\Mappers\UserMapper;
use Neuron\Exceptions\ExpectedType;
use Neuron\MapperFactory;
use CatLab\Accounts\Models\User;
use Neuron\Core\Template;
use Neuron\Core\Tools;
use Neuron\Net\Response;
use Neuron\URLBuilder;

class Password
	extends Authenticator
{
	/**
	 * @return string
	 * @throws \Neuron\Exceptions\DataNotSet
	 */
	public function getForm ()
	{
		$template = new Template ('CatLab/Accounts/authenticators/password/form.phpt');

		$template->set ('action', URLBuilder::getURL ($this->module->getRoutePath () . '/login/' . $this->getToken ()));
		$template->set ('register', URLBuilder::getURL ($this->module->getRoutePath () . '/register/' . $this->getToken ()));

		$template->set ('email', $this->request->input ('email'));

		return $template->parse ();
	}

	/**
	 * @return Response|string
	 */
	public function login ()
	{
		$template = new Template ('CatLab/Accounts/authenticators/password/page.phpt');

		if ($this->request->isPost ())
		{
			$email = $this->request->input ('email', 'email');
			$password = $this->request->input ('password');

			if ($email && $password)
			{
				$response = $this->processLogin ($email, $password);
				if ($response instanceof Response)
				{
					return $response;
				}
				else if (is_string ($response))
				{
					$template->set ('error', $response);
				}
			}
		}

		$template->set ('layout', $this->module->getLayout ());

		$template->set ('action', URLBuilder::getURL ($this->module->getRoutePath () . '/login/' . $this->getToken ()));
		$template->set ('register', URLBuilder::getURL ($this->module->getRoutePath () . '/register/' . $this->getToken ()));

		$template->set ('email', $this->request->input ('email'));

		return Response::template ($template);
	}

	/**
	 * @return bool|Response|string
	 */
	public function register ()
	{
		$template = new Template ('CatLab/Accounts/authenticators/password/register.phpt');

		if ($this->request->isPost ())
		{
			$email = $this->request->input ('email', 'email');
			$username = $this->request->input ('username', 'username');
			$password = $this->request->input ('password');

			$response = $this->processRegister ($email, $username, $password);
			if ($response instanceof Response)
			{
				return $response;
			}
			else if (is_string ($response))
			{
				$template->set ('error', $response);
			}
		}

		$template->set ('layout', $this->module->getLayout ());
		$template->set ('action', URLBuilder::getURL ($this->module->getRoutePath () . '/register/' . $this->getToken ()));
		$template->set ('email', $this->request->input ('email', 'string'));
		$template->set ('username', $this->request->input ('username', 'string'));

		return Response::template ($template);
	}

	/**
	 * Return an error (string) or redirect
	 * @param $email
	 * @param $password
	 * @return string|Response
	 */
	private function processLogin ($email, $password)
	{
		$mapper = MapperFactory::getUserMapper ();
		ExpectedType::check ($mapper, UserMapper::class);

		$user = $mapper->getFromLogin ($email, $password);

		if ($user)
		{
			// Everything okay
			return $this->module->login ($this->request, $user);
		}

		else {
			// Check if we have this email address
			$user = $mapper->getFromEmail ($email);
			if ($user)
			{
				return 'PASSWORD_INCORRECT';
			}
			else {
				return 'USER_NOT_FOUND';
			}
		}
	}

	/**
	 * @param $email
	 * @param $username
	 * @param $password
	 * @return bool|string
	 * @throws \Neuron\Exceptions\InvalidParameter
	 */
	private function processRegister ($email, $username, $password)
	{
		$mapper = MapperFactory::getUserMapper ();
		ExpectedType::check ($mapper, UserMapper::class);

		// Check email invalid
		if (!$email)
		{
			return 'EMAIL_INVALID';
		}

		// Check username input
		if (!$username)
		{
			return 'USERNAME_INVALID';
		}

		// Check if password is good
		if (!Tools::checkInput ($password, 'password'))
		{
			return 'PASSWORD_INVALID';
		}

		// Check if email is unique
		$user = $mapper->getFromEmail ($email);
		if ($user)
		{
			return 'EMAIL_DUPLICATE';
		}

		// Check if username is unique
		$user = $mapper->getFromUsername ($username);
		if ($user)
		{
			return 'USERNAME_DUPLICATE';
		}

		// Create the user
		$user = new User ();
		$user->setEmail ($email);
		$user->setUsername ($username);
		$user->setPassword ($password);

		$user = $mapper->create ($user);
		if ($user)
		{
			return $this->module->login ($this->request, $user);
		}
		else {
			return $mapper->getError ();
		}
	}

}