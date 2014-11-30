<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 29/11/14
 * Time: 10:49
 */

namespace CatLab\Accounts\Controllers;

use CatLab\Accounts\MapperFactory;
use Neuron\Core\Template;
use Neuron\Core\Tools;
use Neuron\Net\Response;
use Neuron\URLBuilder;

class LoginController
	extends Base
{
	public function login ()
	{
		$template = new Template ('CatLab/Accounts/login.phpt');

		$template->set ('layout', $this->module->getLayout ());
		$template->set ('action', URLBuilder::getURL ($this->module->getRoutePath () . '/login'));
		$template->set ('email', $this->request->input ('email'));

		$authenticators = $this->module->getAuthenticators ();
		foreach ($authenticators as $v)
		{
			$v->setRequest ($this->request);
		}

		$template->set ('authenticators', $authenticators);

		return Response::template ($template);
	}

	public function authenticator ($token)
	{
		$authenticator = $this->module->getAuthenticators ()->getFromToken ($token);

        if (!$authenticator)
        {
            return Response::error ('Authenticator not found', Response::STATUS_NOTFOUND);
        }

        $authenticator->setRequest ($this->request);

        return $authenticator->process ();
	}

	private function postLoginRedirect ()
	{
		return Response::redirect (URLBuilder::getURL ('/'));
	}

	public function logout ()
	{
		$template = new Template ('CatLab/Accounts/logout.phpt');

		$template->set ('layout', $this->module->getLayout ());
		$template->set ('action', URLBuilder::getURL ($this->module->getRoutePath () . '/login'));

		return Response::template ($template);
	}
}