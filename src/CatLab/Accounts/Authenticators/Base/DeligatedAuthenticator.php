<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 29/04/15
 * Time: 18:14
 */

namespace CatLab\Accounts\Authenticators\Base;


use CatLab\Accounts\Mappers\UserMapper;
use CatLab\Accounts\Models\DeligatedUser;
use CatLab\Accounts\MapperFactory;
use CatLab\Accounts\Models\User;
use Neuron\Core\Template;
use Neuron\Exceptions\ExpectedType;
use Neuron\Net\Response;
use Neuron\URLBuilder;

abstract class DeligatedAuthenticator
	extends Authenticator {

	protected function initialize ()
	{

	}

	protected function setDeligatedUser (DeligatedUser $deligatedUser) {

		$deligatedUser = MapperFactory::getDeligatedMapper ()->touch ($deligatedUser);
		$this->request->getSession ()->set ('deligated-user-id', $deligatedUser->getId ());

		// Does a user exist?
		if ($deligatedUser->getUser ()) {
			return $this->module->login ($this->request, $deligatedUser->getUser ());
		}
		else {
			return Response::redirect (URLBuilder::getURL ($this->module->getRoutePath () . '/register/facebook'));
		}

	}

	/**
	 * @return DeligatedUser|null
	 * @throws \Neuron\Exceptions\DataNotSet
	 */
	protected function getDeligatedUser () {
		$id = $this->request->getSession ()->get ('deligated-user-id');

		if (!$id) {
			return null;
		}

		$user = MapperFactory::getDeligatedMapper ()->getFromId ($id);

		if ($user) {
			return $user;
		}

		return null;
	}

	/**
	 * @param DeligatedUser $deligatedUser
	 * @param $email
	 * @param $username
	 * @return bool|string
	 * @throws ExpectedType
	 * @throws \Neuron\Exceptions\InvalidParameter
	 */
	private function processRegister (DeligatedUser $deligatedUser, $email, $username)
	{
		$mapper = \Neuron\MapperFactory::getUserMapper ();
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

		$user = $mapper->create ($user);

		// Link the deligated user to this user.
		$deligatedUser->setUser ($user);
		MapperFactory::getDeligatedMapper ()->update ($deligatedUser);

		if ($user)
		{
			return $this->module->login ($this->request, $user);
		}
		else {
			return $mapper->getError ();
		}
	}

	public function register () {

		$this->initialize ();

		$deligatedUser = $this->getDeligatedUser ();
		if (!$deligatedUser) {
			return Response::redirect (URLBuilder::getURL ($this->module->getRoutePath () . '/login/' . $this->getToken ()));
		}

		if ($deligatedUser->getUser ()) {
			return $this->module->login ($this->request, $deligatedUser->getUser ());
		}

		// Check for linking request
		if ($this->request->input ('link')) {
			return $this->linkExitingAccount ($deligatedUser);
		}

		$page = new Template ('CatLab/Accounts/authenticators/deligated/register.phpt');

		$page->set ('deligated', true);
		$page->set ('connect', URLBuilder::getURL ($this->module->getRoutePath () . '/register/' . $this->getToken (), array ('link' => 1)));
		$page->set ('layout', $this->module->getLayout ());
		$page->set ('action', URLBuilder::getURL ($this->module->getRoutePath () . '/register/' . $this->getToken ()));

		// Check for input.
		if ($this->request->isPost ())
		{
			$email = $this->request->input ('email', 'email');
			$username = $this->request->input ('username', 'username');

			$response = $this->processRegister ($deligatedUser, $email, $username);
			if ($response instanceof Response)
			{
				return $response;
			}
			else if (is_string ($response))
			{
				$page->set ('error', $response);
			}
		}

		// Name
		if ($name = $deligatedUser->getWelcomeName ()) {
			$page->set ('name', $name);
		}

		// Email.
		if ($email = $this->request->input ('email')) {
			$page->set ('email', $email);
		}

		else if ($email = $deligatedUser->getEmail ()) {
			$page->set ('email', $email);
		}

		else {
			$page->set ('email', '');
		}

		// Username.
		if ($username = $this->request->input ('username')) {
			$page->set ('username', $username);
		}

		else if ($username = $deligatedUser->getProposedUsername ()) {
			$page->set ('username', $username);
		}

		else {
			$page->set ('username', '');
		}

		return Response::template ($page);
	}

	/**
	 * Return an error (string) or redirect
	 * @param DeligatedUser $deligatedUser
	 * @param $email
	 * @param $password
	 * @return Response|string
	 * @throws ExpectedType
	 */
	private function processLogin (DeligatedUser $deligatedUser, $email, $password)
	{
		$mapper = \Neuron\MapperFactory::getUserMapper ();
		ExpectedType::check ($mapper, UserMapper::class);

		$user = $mapper->getFromLogin ($email, $password);

		if ($user)
		{
			// Everything okay

			// Link the deligated user to this user.
			$deligatedUser->setUser ($user);
			MapperFactory::getDeligatedMapper ()->update ($deligatedUser);

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

	private function linkExitingAccount (DeligatedUser $deligatedUser) {

		$page = new Template ('CatLab/Accounts/authenticators/deligated/link.phpt');

		if ($this->request->isPost ()) {

			$email = $this->request->input ('email');
			$password = $this->request->input ('password');

			$response = $this->processLogin ($deligatedUser, $email, $password);
			if ($response instanceof Response)
			{
				return $response;
			}
			else if (is_string ($response))
			{
				$page->set ('error', $response);
			}

		}

		$page->set ('layout', $this->module->getLayout ());
		$page->set ('action', URLBuilder::getURL ($this->module->getRoutePath () . '/register/' . $this->getToken (), array ('link' => 1)));
		$page->set ('return', URLBuilder::getURL ($this->module->getRoutePath () . '/register/' . $this->getToken ()));

		// Name
		if ($name = $deligatedUser->getWelcomeName ()) {
			$page->set ('name', $name);
		}

		// Email.
		if ($email = $this->request->input ('email')) {
			$page->set ('email', $email);
		}

		else if ($email = $deligatedUser->getEmail ()) {
			$page->set ('email', $email);
		}

		else {
			$page->set ('email', '');
		}

		return Response::template ($page);

	}

	public function getName () {
		return ucfirst ($this->getToken ());
	}

	public function getForm ()
	{
		$url = URLBuilder::getURL ($this->module->getRoutePath () . '/login/' . $this->getToken ());
		$text = sprintf (\Neuron\Tools\Text::getInstance ()->getText ('Login with %s'), $this->getName ());

		return '<p class="authenticator ' . $this->getToken () . '">' .
			'<a href="' . $url . '">' . $text . '</a>' .
			'</p>';
	}

	public function getInlineForm ()
	{
		$url = URLBuilder::getURL ($this->module->getRoutePath () . '/login/' . $this->getToken ());
		$text = sprintf (\Neuron\Tools\Text::getInstance ()->getText ('Login with %s'), $this->getName ());

		return '<p class="authenticator inline navbar-text navbar-left' . $this->getToken () . '">' .
		'<a href="' . $url . '">' . $text . '</a>' .
		'</p>';
	}

}