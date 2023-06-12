<?php

namespace CatLab\Accounts\Tools;

use CatLab\Accounts\MapperFactory;
use Neuron\Interfaces\Models\User;

class RateLimiter
{
    /**
     * @param $key
     * @param $max
     * @param $ttl
     * @return false|void
     */
    public function attempt($key, $max, $ttl)
    {
        $attempts = MapperFactory::getRateLimitMapper()->count($key, $ttl);
        if ($attempts > $max) {
            if (rand() < 0.1 || true) {
                MapperFactory::getRateLimitMapper()->cleanup();
            }
            return false;
        }

        MapperFactory::getRateLimitMapper()->register($key, $this->getIpAddress());
        return true;
    }

    /**
     * @param $action
     * @return false|null
     */
    public function attemptIpRateLimit($action = 'login')
    {
        $key = 'ip:' . $action . ':' . $this->getIpAddress();
        return $this->attempt($key, 120, 3600);
    }

    /**
     * @param User $user
     * @return false|null
     */
    public function attemptLogin(User $user)
    {
        $key = 'user:login:' . $user->getId();
        return $this->attempt($key, 10, 60 * 5);
    }

    /**
     * @param User $user
     * @return false|null
     */
    public function attemptChangePassword(User $user)
    {
        $key = 'user:change-password:' . $user->getId();
        return $this->attempt($key, 10, 60 * 5);
    }

    /**
     * @return string|null
     */
    protected function getIpAddress()
    {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return null;
    }
}
