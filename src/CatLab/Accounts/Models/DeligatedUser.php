<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 29/04/15
 * Time: 17:35
 */

namespace CatLab\Accounts\Models;


use Carbon\Carbon;
use Neuron\Interfaces\Model;

class DeligatedUser
	implements Model {

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var int
	 */
	private $uniqueId;

	/**
	 * @var string
	 */
	private $accessToken;

	/**
	 * @var int
	 */
	private $updatedAt;

	/**
	 * @var int
	 */
	private $createdAt;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $gender;

	/**
	 * @var string
	 */
	private $locale;

	/**
	 * @var string
	 */
	private $email;

	/**
	 * @var Carbon
	 */
	private $birthday;

	/**
	 * @var string
	 */
	private $avatar;

	/**
	 * @var string
	 */
	private $firstname;

	/**
	 * @var string
	 */
	private $lastname;

	/**
	 * @var string
	 */
	private $url;

	const GENDER_MALE = 'MALE';
	const GENDER_FEMALE= 'FEMALE';

	/**
	 * @return string
	 */
	public function getId ()
	{
		return $this->id;
	}

	/**
	 * @param string $id
	 */
	public function setId ($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getType ()
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType ($type)
	{
		$this->type = $type;
	}

	/**
	 * @return int
	 */
	public function getUniqueId ()
	{
		return $this->uniqueId;
	}

	/**
	 * @param int $uniqueId
	 */
	public function setUniqueId ($uniqueId)
	{
		$this->uniqueId = $uniqueId;
	}

	/**
	 * @return string
	 */
	public function getAccessToken ()
	{
		return $this->accessToken;
	}

	/**
	 * @param string $accessToken
	 */
	public function setAccessToken ($accessToken)
	{
		$this->accessToken = $accessToken;
	}

	/**
	 * @return int
	 */
	public function getUpdatedAt ()
	{
		return $this->updatedAt;
	}

	/**
	 * @param int $updatedAt
	 */
	public function setUpdatedAt ($updatedAt)
	{
		$this->updatedAt = $updatedAt;
	}

	/**
	 * @return int
	 */
	public function getCreatedAt ()
	{
		return $this->createdAt;
	}

	/**
	 * @param int $createdAt
	 */
	public function setCreatedAt ($createdAt)
	{
		$this->createdAt = $createdAt;
	}

	/**
	 * @return User
	 */
	public function getUser ()
	{
		return $this->user;
	}

	/**
	 * @param User $user
	 */
	public function setUser ($user)
	{
		$this->user = $user;
	}

	/**
	 * @return string
	 */
	public function getName ()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName ($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getGender ()
	{
		return $this->gender;
	}

	/**
	 * @param string $gender
	 */
	public function setGender ($gender)
	{
		$this->gender = $gender;
	}

	/**
	 * @return string
	 */
	public function getLocale ()
	{
		return $this->locale;
	}

	/**
	 * @param string $locale
	 */
	public function setLocale ($locale)
	{
		$this->locale = $locale;
	}

	/**
	 * @return string
	 */
	public function getEmail ()
	{
		return $this->email;
	}

	/**
	 * @param string $email
	 */
	public function setEmail ($email)
	{
		$this->email = $email;
	}

	/**
	 * @return Carbon
	 */
	public function getBirthday ()
	{
		return $this->birthday;
	}

	/**
	 * @param Carbon $birthday
	 */
	public function setBirthday ($birthday)
	{
		$this->birthday = $birthday;
	}

	/**
	 * @return string
	 */
	public function getAvatar ()
	{
		return $this->avatar;
	}

	/**
	 * @param string $avatar
	 */
	public function setAvatar ($avatar)
	{
		$this->avatar = $avatar;
	}

	/**
	 * @return string
	 */
	public function getFirstname ()
	{
		return $this->firstname;
	}

	/**
	 * @param string $firstname
	 */
	public function setFirstname ($firstname)
	{
		$this->firstname = $firstname;
	}

	/**
	 * @return string
	 */
	public function getLastname ()
	{
		return $this->lastname;
	}

	/**
	 * @param string $lastname
	 */
	public function setLastname ($lastname)
	{
		$this->lastname = $lastname;
	}

	/**
	 * @return string
	 */
	public function getUrl ()
	{
		return $this->url;
	}

	/**
	 * @param string $url
	 */
	public function setUrl ($url)
	{
		$this->url = $url;
	}

	public function getProposedUsername () {
		if ($name = $this->getName ()) {
			return str_replace (' ', '', $name);
		}

		return null;
	}

	public function getWelcomeName () {
		if ($name = $this->getFirstname ())
			return $name;

		else if ($name = $this->getName ())
			return $name;

		else
			return 'Guest';
	}

	public function merge (DeligatedUser $user) {

		if ($user->getAvatar ())
			$this->setAvatar ($user->getAvatar ());

		if ($user->getName ())
			$this->setName ($user->getName ());

		if ($user->getFirstname ())
			$this->setFirstname ($user->getFirstname ());

		if ($user->getLastname ())
			$this->setLastname ($user->getLastname ());

		if ($user->getAccessToken ())
			$this->setAccessToken ($user->getAccessToken ());

		if ($user->getGender ())
			$this->setGender ($user->getGender ());

		if ($user->getLocale ())
			$this->setLOcale ($user->getLocale ());

		if ($user->getEmail ())
			$this->setEmail ($user->getEmail ());

		if ($user->getBirthday ())
			$this->setBirthday ($user->getBirthday ());

		if ($user->getAvatar ())
			$this->setAvatar ($user->getAvatar ());
	}

}