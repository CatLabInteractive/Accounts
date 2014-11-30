<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 30/11/14
 * Time: 16:11
 */

namespace CatLab\Accounts\Authenticators;


use CatLab\Accounts\Module;
use Neuron\Net\Request;

abstract class Authenticator {

	public final function __construct ()
	{

	}

	/** @var Request $request */
	protected $request;

	/** @var Module $module */
	protected $module;

	/** @var string $token */
	private $token;

	public function setModule (Module $module)
	{
		$this->module = $module;
	}

	/**
	 * @param Request $request
	 */
	public function setRequest (Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Set a token
	 * @param $token
	 */
	public function setToken ($token)
	{
		$this->token = $token;
	}

	/**
	 * @return string
	 */
	public function getToken ()
	{
		return $this->token;
	}
}