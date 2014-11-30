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
	private $library = array ();

	public function __construct ()
	{
		// On add,
		$this->on ('add', array ($this, 'onAdd'));
	}

	public function getFromToken ($token)
	{
		if (isset ($this->library[$token]))
		{
			return $this[$this->library[$token]];
		}
	}

	/**
	 * @param Authenticator $authenticator
	 * @param $index
	 */
	protected function onAdd (Authenticator $authenticator, $index)
	{
		$token = $this->generateToken ($authenticator);

		$authenticator->setToken ($token);
		$this->library[$token] = $index;
	}
}