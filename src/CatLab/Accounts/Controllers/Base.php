<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 30/11/14
 * Time: 13:01
 */

namespace CatLab\Accounts\Controllers;

use CatLab\Accounts\ModuleController;
use Neuron\Exceptions\InvalidParameter;
use Neuron\Interfaces\Controller;
use Neuron\Interfaces\Module;
use Neuron\Net\Request;

abstract class Base
	implements Controller
{

	/** @var ModuleController $module */
	protected $module;

	/** @var  Request $request */
	protected $request;

	/**
	 * Controllers must know what module they are from.
	 * @param Module $module
	 * @throws InvalidParameter
	 */
	public function __construct (Module $module = null)
	{
		if (! ($module instanceof ModuleController))
		{
			throw new InvalidParameter ("Controller must be instanciated with a \\CatLab\\Accounts\\ModuleController. Instance of " . get_class ($module) . " given.");
		}

		$this->module = $module;
	}

	/**
	 * Set (or clear) the request object.
	 * @param Request $request
	 * @return void
	 */
	public function setRequest (Request $request = null)
	{
		$this->request = $request;
	}
}