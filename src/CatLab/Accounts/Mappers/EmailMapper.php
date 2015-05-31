<?php

namespace CatLab\Accounts\Mappers;

use CatLab\Accounts\Models\Email;
use DateTime;
use Neuron\DB\Query;
use Neuron\MapperFactory;
use Neuron\Mappers\BaseMapper;

class EmailMapper
	extends BaseMapper {

	/**
	 * @param $id
	 * @return Email|null
	 */
	public function getFromId ($id)
	{
		$query = Query::select (
			'neuron_users_emails',
			array ('*'),
			array (
				'ue_id' => $id
			)
		);

		return $this->getSingle ($query->execute ());
	}

	/**
	 * @param Email $email
	 */
	public function create (Email $email)
	{
		$id = Query::insert (
			'neuron_users_emails',
			array (
				'u_id' => $email->getUser ()->getId (),
				'ue_email' => $email->getEmail (),
				'ue_verified' => $email->isVerified () ? 1 : 0,
				'ue_token' => $email->getToken (),
				'ue_expires' => $email->getExpires ()
			)
		)->execute ();

		$email->setId (intval ($id));
	}

	/**
	 * @param $data
	 * @return Email
	 */
	protected function getObjectFromData ($data)
	{
		$email = new Email (intval ($data['ue_id']));

		$email->setUser (MapperFactory::getUserMapper ()->getFromId ($data['u_id']));
		$email->setEmail ($data['ue_email']);
		$email->setVerified ($data['ue_verified'] == 1);
		$email->setToken ($data['ue_token']);
		$email->setExpires (new DateTime ($data['ue_expires']));

		return $email;
	}
}