<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 30/11/14
 * Time: 15:20
 */
namespace CatLab\Accounts\Mappers;

use CatLab\Accounts\Models\User;
use Neuron\DB\Query;
use Neuron\Exceptions\InvalidParameter;
use Neuron\Mappers\BaseMapper;

class UserMapper
	extends BaseMapper
	implements \CatLab\Accounts\Interfaces\UserMapper
{
	private $table_users;

	/** @var string $error */
	private $error;

	public function __construct ()
	{
		$this->table_users = $this->getTableName ('users');
	}

	/**
	 * @param int $id
	 * @return \CatLab\Accounts\Models\User|null
	 */
	public function getFromId ($id)
	{
		$query = new Query
		("
			SELECT
				*
			FROM
				{$this->table_users}
			WHERE
				u_id = ?
		");

		$query->bindValue (1, $id, Query::PARAM_NUMBER);

		return $this->getSingle ($query->execute ());
	}

	/**
	 * @param $email
	 * @return \CatLab\Accounts\Models\User|null
	 */
	public function getFromEmail ($email)
	{
		$query = new Query
		("
			SELECT
				*
			FROM
				{$this->table_users}
			WHERE
				u_email = ?
		");

		$query->bindValue (1, $email);

		return $this->getSingle ($query->execute ());
	}

	/**
	 * @param $username
	 * @return \CatLab\Accounts\Models\User|null
	 */
	public function getFromUsername ($username)
	{
		$query = new Query
		("
			SELECT
				*
			FROM
				{$this->table_users}
			WHERE
				u_username = ?
		");

		$query->bindValue (1, $username);

		return $this->getSingle ($query->execute ());
	}

	/**
	 * @param $email
	 * @param $password
	 * @return \CatLab\Accounts\Models\User|null
	 */
	public function getFromLogin ($email, $password)
	{
		$user = $this->getFromEmail ($email);

		if ($user)
		{
			if (password_verify ($password, $user->getPasswordHash ()))
			{
				return $user;
			}
			else {
				return null;
			}
		}

		return null;
	}

	/**
	 * @param User $user
	 * @return array
	 */
	private function prepareFields (User $user)
	{
		// Prepare data
		$data = array ();

		// Email
		if ($email = $user->getEmail ())
			$data['u_email'] = $email;

		// Password
		if ($password = $user->getPassword ())
			$data['u_password'] = password_hash ($password, PASSWORD_DEFAULT);

		else if ($hash = $user->getPasswordHash ())
			$data['u_password'] = $hash;

		// Username
		if ($username = $user->getUsername ())
			$data['u_username'] = $username;

		return $data;
	}

	/**
	 * @param User $user
	 * @throws InvalidParameter
	 * @return \CatLab\Accounts\Models\User
	 */
	public function create (User $user)
	{
		// Check for duplicate
		if ($this->getFromEmail ($user->getEmail ()))
			throw new InvalidParameter ("A user with this email address already exists.");

		$data = $this->prepareFields ($user);

		// Insert
		$id = Query::insert ($this->table_users, $data)->execute ();
		$user->setId ($id);

		return $user;
	}

	/**
	 * @param User $user
	 * @return User
	 */
	public function update (User $user)
	{
		$data = $this->prepareFields ($user);
		Query::update ($this->table_users, $data, array ('u_id' => $user->getId ()))->execute ();
	}

	protected function getModelInstance ()
	{
		return new User ();
	}

	/**
	 * @param $data
	 * @return User
	 */
	protected function getObjectFromData ($data)
	{
		$user = $this->getModelInstance ();

		$user->setId ($data['u_id']);

		if ($data['u_email'])
			$user->setEmail ($data['u_email']);

		if ($data['u_password'])
			$user->setPasswordHash ($data['u_password']);

		if ($data['u_username'])
			$user->setUsername ($data['u_username']);

		return $user;
	}

	/**
	 * @return string
	 */
	public function getError ()
	{
		return $this->error;
	}
}