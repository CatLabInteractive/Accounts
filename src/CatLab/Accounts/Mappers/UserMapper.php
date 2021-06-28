<?php

namespace CatLab\Accounts\Mappers;

use CatLab\Accounts\MapperFactory;
use CatLab\Accounts\Models\User;
use CatLab\Base\Models\Database\DB;
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

    public function __construct()
    {
        $this->table_users = $this->getTableName('users');
    }

    /**
     * @param int $id
     * @return \CatLab\Accounts\Models\User|null
     */
    public function getFromId($id)
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

        $query->bindValue(1, $id, Query::PARAM_NUMBER);

        return $this->getSingle($query->execute());
    }

    /**
     * @param $email
     * @return \CatLab\Accounts\Models\User|null
     */
    public function getFromEmail($email)
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

        $query->bindValue(1, $email);

        return $this->getSingle($query->execute());
    }

    /**
     * @param $email
     * @param $password
     * @return \CatLab\Accounts\Models\User|null
     */
    public function getFromLogin($email, $password)
    {
        $user = $this->getFromEmail($email);

        if ($user) {
            if (password_verify($password, $user->getPasswordHash())) {
                return $user;
            } else {
                return null;
            }
        }

        return null;
    }

    /**
     * @param User $user
     * @return array
     */
    protected function getDataToSet(User $user)
    {
        // Prepare data
        $data = array();

        // Email
        if ($email = $user->getEmail())
            $data['u_email'] = $email;

        // Password
        if ($password = $user->getPassword())
            $data['u_password'] = password_hash($password, PASSWORD_DEFAULT);

        else if ($hash = $user->getPasswordHash())
            $data['u_password'] = $hash;

        // Username
        if ($firstName = $user->getFirstName())
            $data['u_firstName'] = $firstName;

        if ($familyName = $user->getFamilyName())
            $data['u_familyName'] = $familyName;

        if ($birthDate = $user->getBirthDate()) {
            $data['u_birthdate'] = [ $birthDate, Query::PARAM_DATE ];
        }

        $data['u_emailVerified'] = $user->isEmailVerified() ? 1 : 0;

        return $data;
    }

    /**
     * @param User $user
     * @throws InvalidParameter
     * @return \CatLab\Accounts\Models\User
     */
    public function create(User $user)
    {
        // Check for duplicate
        if ($this->getFromEmail($user->getEmail()))
            throw new InvalidParameter ("A user with this email address already exists.");

        $data = $this->getDataToSet($user);
        $data['created_at'] = [ new \DateTime(), Query::PARAM_DATE ];

        // Insert
        $id = Query::insert($this->table_users, $data)->execute();
        $user->setId($id);

        return $user;
    }

    /**
     * @param User $user
     * @return User
     */
    public function update(User $user)
    {
        $data = $this->getDataToSet($user);
        Query::update($this->table_users, $data, array('u_id' => $user->getId()))->execute();
    }

    protected function getModelInstance()
    {
        return new User ();
    }

    /**
     * @param $data
     * @return User
     */
    protected function getObjectFromData($data)
    {
        $user = $this->getModelInstance();

        $user->setId(intval($data['u_id']));

        if ($data['u_email'])
            $user->setEmail($data['u_email']);

        if ($data['u_password'])
            $user->setPasswordHash($data['u_password']);

        if ($data['u_firstName'])
            $user->setFirstName($data['u_firstName']);

        if ($data['u_familyName'])
            $user->setFamilyName($data['u_familyName']);

        $user->setEmailVerified($data['u_emailVerified'] == 1);

        return $user;
    }

    /**
     * @param User $user
     */
    public function anonymize(User $user)
    {
        Query::update($this->getTableName('users'), [

            'u_email' => null,
            'u_password' => null,
            'u_firstName' => null,
            'u_familyName' => null,
            'u_emailVerified' => null

        ], [ 'u_id' => $user->getId() ])->execute();

        MapperFactory::getDeligatedMapper()->deleteFromUser($user);
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}
