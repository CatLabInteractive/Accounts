<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 30/11/14
 * Time: 15:23
 */

namespace CatLab\Accounts\Models;

class User {

	/** @var int $id */
	private $id;

	/** @var string $email */
	private $email;

	/** @var string $password */
	private $password;

	public function __construct ()
	{

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
	 */
	public function setId ($id)
	{
		$this->id = $id;
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
	 */
	public function setEmail ($email)
	{
		$this->email = $email;
	}

	/**
	 * @return string
	 */
	public function getPassword ()
	{
		return $this->password;
	}

	/**
	 * @param string $password
	 */
	public function setPassword ($password)
	{
		$this->password = $password;
	}


}