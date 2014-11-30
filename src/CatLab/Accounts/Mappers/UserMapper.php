<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 30/11/14
 * Time: 15:20
 */
namespace CatLab\Accounts\Mappers;

use CatLab\Accounts\Models\User;

class UserMapper
	implements \CatLab\Accounts\Interfaces\UserMapper
{

	/**
	 * @param $email
	 * @param $password
	 * @return \CatLab\Accounts\Models\User|null
	 */
	public function getFromLogin ($email, $password)
	{
		return null;
	}

	/**
	 * @param $email
	 * @return \CatLab\Accounts\Models\User|null
	 */
	public function getFromEmail ($email)
	{
		return null;
	}

	/**
	 * @param User $user
	 * @return User
	 */
	public function create (User $user)
	{
		// TODO: Implement create() method.
	}
}