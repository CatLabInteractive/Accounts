<?php

namespace CatLab\Accounts;

/**
 * Class MapperFactory
 * @package CatLab\Accounts
 */
class MapperFactory
{
    /**
     * @return MapperFactory
     */
    public static function getInstance()
    {
        static $in;
        if (!isset ($in)) {
            $in = new self ();
        }
        return $in;
    }

    private $mapped = array();

    /**
     * @param $key
     * @param $mapper
     */
    public function setMapper($key, $mapper)
    {
        $this->mapped[$key] = $mapper;
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    public function getMapper($key, $default)
    {
        if (isset ($this->mapped[$key])) {
            return $this->mapped[$key];
        } else {
            $this->mapped[$key] = new $default ();
        }
        return $this->mapped[$key];
    }

    /**
     * @return \CatLab\Accounts\Mappers\DeligatedMapper
     */
    public static function getDeligatedMapper()
    {
        return self::getInstance()->getMapper('deligated', '\CatLab\Accounts\Mappers\DeligatedMapper');
    }

    /**
     * @return \CatLab\Accounts\Mappers\EmailMapper
     */
    public static function getEmailMapper()
    {
        return self::getInstance()->getMapper('emails', '\CatLab\Accounts\Mappers\EmailMapper');
    }

    /**
     * @return \CatLab\Accounts\Mappers\PasswordRecoveryMapper
     */
    public static function getPasswordRecoveryMapper()
    {
        return self::getInstance()->getMapper('passwordRecovery', '\CatLab\Accounts\Mappers\PasswordRecoveryMapper');
    }

    /**
     * @return \CatLab\Accounts\Mappers\RateLimitMapper
     */
    public static function getRateLimitMapper()
    {
        return self::getInstance()->getMapper('rateLimitMapper', '\CatLab\Accounts\Mappers\RateLimitMapper');
    }
}
