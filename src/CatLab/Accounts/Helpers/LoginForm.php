<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 29/11/14
 * Time: 17:27
 */

namespace CatLab\Accounts\Helpers;

use CatLab\Accounts\Module;
use Neuron\Application;
use Neuron\Core\Template;
use Neuron\Core\Tools;
use Neuron\MapperFactory;
use Neuron\URLBuilder;

class LoginForm {

	private $moduleController;

	/**
	 * @param Module $controller
	 */
	public function __construct (Module $controller)
	{
		$this->moduleController = $controller;
	}

	/**
	 * @return string
	 */
	public function helper ()
	{
		$request = Application::getInstance ()->getRouter ()->getRequest ();

		$user = $request->getUser ();
		if (!$user) {

			// The helper should also check for non verified users.
			$userId = $request->getSession ()->get ('catlab-non-verified-user-id');
			if ($userId) {
				$user = MapperFactory::getUserMapper ()->getFromId ($userId);
			}

		}

		if ($user)
		{
			$template = new Template ('CatLab/Accounts/helpers/welcome.phpt');
			$template->set ('user', $user);
			$template->set ('logout', URLBuilder::getURL ($this->moduleController->getRoutePath ()  . '/logout'));
			return $template;
		}
		else {

			$template = new Template ('CatLab/Accounts/helpers/form-small.phpt');

			$authenticators = $this->moduleController->getAuthenticators ();
			$authenticators->setRequest ($request);

			$template->set ('authenticators', $authenticators);

			return $template;
		}
	}
}