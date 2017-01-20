<?php

namespace CatLab\Accounts\Models;

use DateTime;
use Neuron\URLBuilder;

/**
 * Class PasswordRecovery
 * @package CatLab\Accounts\Models
 */
class PasswordRecovery
{
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
    private $token;

    /**
     * @var DateTime
     */
    private $expires;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return PasswordRecovery
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return PasswordRecovery
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return PasswordRecovery
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param DateTime $expires
     * @return PasswordRecovery
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isExpired()
    {
        return $this->getExpires()->getTimestamp() < time();
    }

    /**
     * @param $rootPath
     * @return string
     */
    public function getUrl($rootPath)
    {
        $params = array(
            'lostPassword' => 2,
            'id' => $this->getId(),
            'token' => $this->getToken()
        );

        return URLBuilder::getAbsoluteURL($rootPath . '/login/password', $params);
    }
}