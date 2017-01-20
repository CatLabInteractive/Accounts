<?php

namespace CatLab\Accounts\Models;

use DateTime;
use Neuron\Interfaces\Models\User;
use Neuron\Interfaces\Model;
use Neuron\URLBuilder;

class Email
	implements Model {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var string
	 */
	private $email;

	/**
	 * @var boolean
	 */
	private $verified;

	/**
	 * @var string
	 */
	private $token;

	/**
	 * @var DateTime
	 */
	private $expires;

	public function __construct ($id = null)
	{
		if (isset ($id)) {
			$this->setId ($id);
		}
	}

	/**
	 * @return int
	 */
	public function getId ()
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return self
	 */
	public function setId ($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return User
	 */
	public function getUser ()
	{
		return $this->user;
	}

	/**
	 * @param User $user
	 * @return self
	 */
	public function setUser ($user)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmail ()
	{
		return $this->email;
	}

	/**
	 * @param string $email
	 * @return self
	 */
	public function setEmail ($email)
	{
		$this->email = $email;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isVerified ()
	{
		return $this->verified;
	}

	/**
	 * @param boolean $verified
	 * @return self
	 */
	public function setVerified ($verified)
	{
		$this->verified = $verified;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getToken ()
	{
		return $this->token;
	}

	/**
	 * @param string $token
	 * @return self
	 */
	public function setToken ($token)
	{
		$this->token = $token;
		return $this;
	}

	/**
	 * @return DateTime
	 */
	public function getExpires ()
	{
		return $this->expires;
	}

	/**
	 * @param DateTime $expires
	 * @return self
	 */
	public function setExpires ($expires)
	{
		$this->expires = $expires;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isExpired ()
	{
		return $this->getExpires ()->getTimestamp () < time ();
	}

    /**
     * @param $rootPath
     * @return string
     */
	public function getVerifyURL ($rootPath)
	{
		$params = array (
			'token' => $this->getToken ()
		);

		return URLBuilder::getAbsoluteURL ($rootPath . '/verify/' . $this->getId (), $params);
	}

}