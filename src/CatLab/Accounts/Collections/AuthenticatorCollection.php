<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 30/11/14
 * Time: 18:09
 */
namespace CatLab\Accounts\Collections;

use CatLab\Accounts\Authenticators\Authenticator;
use Neuron\Collections\TokenizedCollection;

class AuthenticatorCollection
	extends TokenizedCollection
{

	public function __construct ()
	{
		// On add,
		$this->on ('add', array ($this, 'onAdd'));
	}

	/**
	 * @param Authenticator $authenticator
	 */
	protected function onAdd (Authenticator $authenticator)
	{
		$authenticator->setToken ($this->generateToken ($authenticator));
	}
}