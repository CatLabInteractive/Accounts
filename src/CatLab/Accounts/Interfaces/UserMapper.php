<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 30/11/14
 * Time: 20:43
 */

namespace CatLab\Accounts\Interfaces;

use CatLab\Accounts\Models\User;

interface UserMapper {

	/**
	 * @param User $user
	 * @return User
	 */
	public function create (User $user);

	/**
	 * @param $email
	 * @param $password
	 * @return \CatLab\Accounts\Models\User|null
	 */
	public function getFromLogin ($email, $password);

	/**
	 * @param $email
	 * @return \CatLab\Accounts\Models\User|null
	 */
	public function getFromEmail ($email);

}