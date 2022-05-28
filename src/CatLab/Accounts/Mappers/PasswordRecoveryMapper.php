<?php

namespace CatLab\Accounts\Mappers;

use CatLab\Accounts\Models\Email;
use CatLab\Accounts\Models\PasswordRecovery;
use CatLab\Accounts\Models\User;
use DateTime;
use Neuron\DB\Query;
use Neuron\MapperFactory;
use Neuron\Mappers\BaseMapper;

/**
 * Class PasswordRecoveryMapper
 * @package CatLab\Accounts\Mappers
 */
class PasswordRecoveryMapper extends BaseMapper
{
    /**
     * @param $id
     * @return PasswordRecovery|null
     */
    public function getFromId($id)
    {
        $query = Query::select(
            'neuron_users_password_recovery',
            array('*'),
            array(
                'nupr_id' => $id
            )
        );

        return $this->getSingle($query->execute());
    }

    /**
     * @param User $user
     * @return bool
     */
    public function hasRecentActiveRecoveryRequest(User $user)
    {
        $query = new Query("
            SELECT
                nupr_id
            FROM
                neuron_users_password_recovery
            WHERE
                u_id = ? AND 
                nupr_expires < ?
        ");

        $query->bindValue(1, $user->getId());
        $query->bindValue(2, new DateTime(), Query::PARAM_DATE);

        return count($query->execute()) > 0;
    }

    /**
     * @param PasswordRecovery $request
     */
    public function create(PasswordRecovery $request)
    {
        $id = Query::insert(
            'neuron_users_password_recovery',
            array(
                'u_id' => $request->getUser()->getId(),
                'nupr_token' => $request->getToken(),
                'nupr_expires' => $request->getExpires()
            )
        )->execute();

        $request->setId(intval($id));
    }

    /**
     * @param $data
     * @return PasswordRecovery
     */
    protected function getObjectFromData($data)
    {
        $email = new PasswordRecovery(intval($data['ue_id']));

        /** @var User $user */
        $user = MapperFactory::getUserMapper()->getFromId($data['u_id']);
        $email->setUser($user);

        $email->setToken($data['nupr_token']);
        $email->setExpires(new DateTime ($data['nupr_expires']));

        return $email;
    }
}
