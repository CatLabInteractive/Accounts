<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 30/11/14
 * Time: 13:01
 */

namespace CatLab\Accounts\Controllers;

use CatLab\Accounts\Module;
use Neuron\Exceptions\InvalidParameter;
use Neuron\Interfaces\Controller;
use Neuron\Net\Request;
use Neuron\Tools\TokenGenerator;

abstract class Base
	implements Controller
{
	/** @var Module $module */
	protected $module;

	/** @var  Request $request */
	protected $request;

	/**
	 * Controllers must know what module they are from.
	 * @param \Neuron\Interfaces\Module $module
	 * @throws InvalidParameter
	 */
	public function __construct (\Neuron\Interfaces\Module $module = null)
	{
		if (! ($module instanceof Module))
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

    /**
     * @return bool
     * @throws \Neuron\Exceptions\DataNotSet
     */
    public static function isValidCsfrToken(Request $request)
    {
        if (!$request->getSession()->get('accounts-csfr-token')) {
            return false;
        }

        if ($request->input('csfr-token') !== $request->getSession()->get('accounts-csfr-token')) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     * @throws \Neuron\Exceptions\DataNotSet
     */
    public static function generateCsfrToken(Request $request)
    {
        if (!$request->getSession()->get('accounts-csfr-token')) {
            $csfr = TokenGenerator::getToken(32);
            $request->getSession()->set('accounts-csfr-token', $csfr);
        }

        return $request->getSession()->get('accounts-csfr-token');
    }
}
