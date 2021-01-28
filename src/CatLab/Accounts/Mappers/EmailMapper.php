<?php

namespace CatLab\Accounts\Mappers;

use Accounts\Models\User;
use CatLab\Accounts\Models\Email;
use DateTime;
use Neuron\DB\Query;
use Neuron\MapperFactory;
use Neuron\Mappers\BaseMapper;

class EmailMapper
    extends BaseMapper
{
    /**
     * @param $id
     * @return Email|null
     */
    public function getFromId($id)
    {
        $query = Query::select(
            'neuron_users_emails',
            array('*'),
            array(
                'ue_id' => $id
            )
        );

        return $this->getSingle($query->execute());
    }

    /**
     * @param User $user
     * @return array|mixed[]|\Neuron\Collections\Collection
     */
    public function getFromUser(User $user)
    {
        $query = Query::select(
            'neuron_users_emails',
            array('*'),
            array(
                'u_id' => $user->getId()
            )
        );

        return $this->getObjectsFromData($query->execute());
    }

    /**
     * @param Email $email
     */
    public function create(Email $email)
    {
        $data = $this->getDataToSet($email);

        $id = Query::insert(
            'neuron_users_emails',
            $data
        )->execute();

        $email->setId(intval($id));
    }

    /**
     * @param Email $email
     */
    public function update(Email $email)
    {
        Query::update(
            'neuron_users_emails',
            $this->getDataToSet($email),
            [
                'ue_id' => $email->getId()
            ]
        )->execute();
    }

    /**
     * @param Email $email
     * @return array
     */
    protected function getDataToSet(Email $email)
    {
        return array(
            'u_id' => $email->getUser()->getId(),
            'ue_email' => $email->getEmail(),
            'ue_verified' => $email->isVerified() ? 1 : 0,
            'ue_token' => $email->getToken(),
            'ue_expires' => $email->getExpires()
        );
    }

    /**
     * @param $emailAddress
     */
    public function removeForEmailAddress($emailAddress)
    {
        Query::delete('neuron_users_emails', [
            'ue_email' => $emailAddress
        ])->execute();
    }

    /**
     * @param User $user
     */
    public function removeForUser(User $user)
    {
        Query::delete('neuron_users_emails', [
            'u_id' => $user->getId()
        ])->execute();
    }

    /**
     * @param $data
     * @return Email
     * @throws \Exception
     */
    protected function getObjectFromData($data)
    {
        $email = new Email (intval($data['ue_id']));

        $email->setUser(MapperFactory::getUserMapper()->getFromId($data['u_id']));
        $email->setEmail($data['ue_email']);
        $email->setVerified($data['ue_verified'] == 1);
        $email->setToken($data['ue_token']);
        $email->setExpires(new DateTime ($data['ue_expires']));

        return $email;
    }
}
