<?php

namespace CatLab\Accounts\Mappers;

use Neuron\DB\Query;

/**
 *
 */
class RateLimitMapper
{
    public function count($key, $ttl)
    {
        $query = new Query("
            SELECT
                COUNT(*) AS count
            FROM
                neuron_rate_limiter
            WHERE
                rl_key = ?
                AND created_at > ?
       ");

        $query->bindValue(1, $key);
        $query->bindValue(2, time() - $ttl, Query::PARAM_DATE);

        $result = $query->execute();
        if (count($result) == 0) {
            return 0;
        }
        return intval($result[0]['count']);
    }

    public function register($key, $ipAddress)
    {
        Query::insert('neuron_rate_limiter', [
            'rl_key' => $key,
            'rl_ip_address' => inet_pton($ipAddress),
            'created_at' => array(time(), Query::PARAM_DATE)
        ])->execute();
    }

    public function cleanup()
    {
        $query = new Query("
            DELETE FROM
                neuron_rate_limiter
            WHERE
                created_at < ?");

        $query->bindValue(1, time() - 24 * 3600, Query::PARAM_DATE);
        $query->execute();
    }
}
