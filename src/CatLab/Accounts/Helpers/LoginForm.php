<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 29/11/14
 * Time: 17:27
 */

namespace CatLab\Accounts\Helpers;

use CatLab\Accounts\Module;
use Neuron\Core\Template;
use Neuron\Core\Tools;

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
		$template = new Template ('CatLab/Accounts/helpers/form-small.phpt');

		$template->set ('action', '');
		$template->set ('email', Tools::getInput ($_POST, 'email', 'varchar'));

		return $template->parse ();
	}
}