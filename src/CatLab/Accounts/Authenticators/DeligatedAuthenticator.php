<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 29/04/15
 * Time: 18:14
 */

namespace CatLab\Accounts\Authenticators;


use CatLab\Accounts\Models\DeligatedUser;
use CatLab\Accounts\MapperFactory;
use Neuron\Core\Template;
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

	public function register () {

		var_dump ($this->getDeligatedUser ());
		exit;

		$this->initialize ();

		$deligatedUser = $this->getDeligatedUser ();
		if (!$deligatedUser) {
			return Response::redirect (URLBuilder::getURL ($this->module->getRoutePath () . '/login/' . $this->getToken ()));
		}

		if ($deligatedUser->getUser ()) {
			return $this->module->login ($this->request, $deligatedUser->getUser ());
		}

		$template = new Template ('CatLab/Accounts/authenticators/deligated/register.phpt');
		$template->set ('layout', $this->module->getLayout ());

		var_dump ($deligatedUser);

		return Response::template ($template);
	}

}