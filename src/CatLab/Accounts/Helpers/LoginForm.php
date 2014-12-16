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
	public function smallForm ()
	{
		$request = Application::getInstance ()->getRouter ()->getRequest ();

		if ($user = $request->getUser ())
		{
			$template = new Template ('CatLab/Accounts/helpers/welcome.phpt');
			$template->set ('user', $user);
			$template->set ('logout', URLBuilder::getURL ($this->moduleController->getRoutePath ()  . '/logout'));
			return $template;
		}
		else {

			$template = new Template ('CatLab/Accounts/helpers/form-small.phpt');

			$template->set ('action', URLBuilder::getURL ($this->moduleController->getRoutePath () . '/login/password', array ('return' => $request->getUrl ())));
			$template->set ('email', Tools::getInput ($_POST, 'email', 'varchar'));

			return $template;
		}
	}
}