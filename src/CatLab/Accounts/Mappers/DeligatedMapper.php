<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 29/04/15
 * Time: 17:34
 */

namespace CatLab\Accounts\Mappers;


use Carbon\Carbon;
use CatLab\Accounts\Models\DeligatedUser;
use CatLab\Accounts\Models\User;
use DateTime;
use Neuron\DB\Query;
use Neuron\Exceptions\InvalidParameter;
use Neuron\Mappers\BaseMapper;

class DeligatedMapper
	extends BaseMapper {

	/**
	 * @param $type
	 * @param $id
	 * @return DeligatedUser|null
	 */
	public function getFromUniqueID ($type, $id) {

		$query = Query::select (
			'neuron_users_deligated',
			array ('*'),
			array (
				'ud_type' => $type,
				'ud_unique_id' => $id
			)
		);

		return $this->getSingle ($query->execute ());

	}

	public function getFromId ($id) {
		$query = Query::select (
			'neuron_users_deligated',
			array ('*'),
			array (
				'ud_id' => $id
			)
		);

		return $this->getSingle ($query->execute ());
	}

	public function getFromUser (User $user, $type = null) {

		$where = array (
			'u_id' => $user->getId ()
		);

		if ($type) {
			$where['ud_type'] = $type;
		}

		$query = Query::select (
			'neuron_users_deligated',
			array ('*'),
			$where
		);

		return $this->getObjectsFromData ($query->execute ());
	}

	public function update (DeligatedUser $user) {

		if (!$user->getId ()) {
			throw new InvalidParameter ("DeligatedUser must have an id before updating.");
		}

		Query::update (
			'neuron_users_deligated',
			$this->getDataToSet ($user),
			array (
				'ud_id' => $user->getId ()
			)
		)->execute ();

	}

	private function getDataToSet (DeligatedUser $user) {
		$out = array ();

		if ($user->getUser ())
			$out['u_id'] = $user->getUser ()->getId ();

		$out['ud_type'] = $user->getType ();
		$out['ud_unique_id'] = $user->getUniqueId ();

		if ($name = $user->getName ())
			$out['ud_name'] = $name;

		if ($accessToken = $user->getAccessToken ())
			$out['ud_access_token'] = $accessToken;

		if ($gender = $user->getGender ())
			$out['ud_gender'] = $gender;

		if ($email = $user->getEmail ())
			$out['ud_email'] = $email;

		if ($birthday = $user->getBirthday ())
			$out['ud_birthday'] = $birthday;

		if ($locale = $user->getLocale ())
			$out['ud_locale'] = $locale;

		if ($avatar = $user->getAvatar ())
			$out['ud_avatar'] = $avatar;

		if ($firstname = $user->getFirstname ())
			$out['ud_firstname'] = $firstname;

		if ($lastname = $user->getLastname ())
			$out['ud_lastname'] = $lastname;

		$out['updated_at'] = new DateTime ();

		return $out;
	}

	public function create (DeligatedUser $user) {

		if (!$user->getUniqueId () || !$user->getType ()) {
			throw new InvalidParameter ("All DeligatedUsers must have types and unique ids.");
		}

		$set = $this->getDataToSet ($user);
		$set['created_at'] = new DateTime ();

		$id = Query::insert (
			'neuron_users_deligated',
			$set
		)->execute ();

		$user->setId (intval ($id));

	}

	public function touch (DeligatedUser $user) {

		// Check if we already have this one.
		$original = $this->getFromUniqueID ($user->getType (), $user->getUniqueId ());
		if ($original) {
			$original->merge ($user);
			$this->update ($original);

			return $original;
		}

		else {
			$this->create ($user);
			return $user;
		}
	}

	protected function getObjectFromData ($data)
	{

		$deligatedUser = new DeligatedUser ();
		$deligatedUser->setId (intval ($data['ud_id']));
		$deligatedUser->setType ($data['ud_type']);
		$deligatedUser->setUniqueId ($data['ud_unique_id']);

		if ($data['ud_name'])
			$deligatedUser->setName ($data['ud_name']);

		if ($data['ud_access_token'])
			$deligatedUser->setAccessToken ($data['ud_access_token']);

		if ($data['ud_gender'])
			$deligatedUser->setGender ($data['ud_gender']);

		if ($data['ud_email'])
			$deligatedUser->setEmail ($data['ud_email']);

		if ($data['ud_birthday']) {
			$deligatedUser->setBirthday (new Carbon ($data['ud_birthday']));
		}

		if ($data['ud_locale'])
			$deligatedUser->setLocale ($data['ud_locale']);

		if ($data['ud_avatar'])
			$deligatedUser->setAvatar ($data['ud_avatar']);

		if ($data['ud_firstname'])
			$deligatedUser->setFirstname ($data['ud_firstname']);

		if ($data['ud_lastname'])
			$deligatedUser->setLastname ($data['ud_lastname']);

		if ($data['u_id']) {
			$user = \Neuron\MapperFactory::getUserMapper ()->getFromId ($data['u_id']);
			if ($user) {
				$deligatedUser->setUser ($user);
			}
		}

		$deligatedUser->setCreatedAt (strtotime ($data['created_at']));
		$deligatedUser->setUpdatedAt (strtotime ($data['updated_at']));

		return $deligatedUser;
	}
}