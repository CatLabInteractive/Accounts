<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 30/11/14
 * Time: 16:11
 */

namespace CatLab\Accounts\Authenticators\Base;


use CatLab\Accounts\Module;
use Neuron\Net\Request;
use Neuron\Net\Response;

abstract class Authenticator {

	public final function __construct ()
	{

	}

	/**
     * @var Request $request
     */
	protected $request;

	/**
     * @var Module $module
     */
	protected $module;

	/**
     * @var string $token
     */
	private $token;

    /**
     * @param Module $module
     */
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

	/**
	 * @return Response
	 */
	public function register ()
	{
		return Response::error ('Authenticator does not have register method.', Response::STATUS_NOTFOUND);
	}

	public abstract function login ();

	public abstract function getForm ();

	public abstract function getInlineForm ();
}