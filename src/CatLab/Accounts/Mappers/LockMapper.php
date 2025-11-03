<?php


namespace CatLab\Accounts\Mappers;

use CatLab\Accounts\Models\Lock;
use Neuron\DB\Database;
use Neuron\DB\Query;
use Neuron\Exceptions\DbException;

/**
 * Class LockMapper
 * @package Accounts\Mappers
 */
class LockMapper
{
    /**
     * @param $name
     * @return Lock|null
     */
    public function create($name)
    {
        $attempts = 50;

        while ($attempts > 0) {
            try {
                Database::getInstance()->start();

                $lockId = Query::insert('locks', [
                    'lock_name' => $name,
                    'created_at' => [new \DateTime(), Query::PARAM_DATE],
                    'updated_at' => [new \DateTime(), Query::PARAM_DATE]
                ])->execute();

                Database::getInstance()->commit();

                $lock = new Lock();
                $lock->setId(intval($lockId));
                $lock->setName($name);
                return $lock;

            } catch (DbException $e) {
                Database::getInstance()->rollback();;
                $attempts --;
                usleep(100000); // 1/10th of  a second
            }
        }

        return null;
    }

    /**
     * @param Lock $lock
     */
    public function delete(Lock $lock)
    {
        Query::delete('locks', [
            'lock_id' => $lock->getId()
        ])->execute();
    }
}
