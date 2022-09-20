<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 29/04/15
 * Time: 16:44
 */

namespace CatLab\Accounts\Authenticators;

use Carbon\Carbon;

use CatLab\Accounts\Authenticators\Base\DeligatedAuthenticator;
use CatLab\Accounts\Models\DeligatedUser;

use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;

use Neuron\Config;
use Neuron\Net\Response;
use Neuron\URLBuilder;

/**
 * Class Facebook
 * @package CatLab\Accounts\Authenticators
 */
class Facebook
	extends DeligatedAuthenticator {

	private $loaded = false;

	/** @var string[] */
	private $scopes = array ('email');

    protected $trustProvidedEmailAddress = true;

	protected function initialize () {

		if (!$this->loaded) {
			FacebookSession::setDefaultApplication (
				Config::get ('accounts.facebook.id'),
				Config::get ('accounts.facebook.secret')
			);
			$this->loaded = true;
		}

	}

	/**
	 * @param string[] $scope
	 */
	public function setScopes (array $scope) {
		$this->scopes = $scope;
	}

	/**
	 * @param string $scope
	 */
	public function addScope ($scope) {
		$this->scopes[] = $scope;
	}

	public function login ()
	{
		$this->initialize ();

		$helper = new FacebookRedirectLoginHelper (
			URLBuilder::getAbsoluteURL (
				$this->module->getRoutePath () . '/login/' . $this->getToken (),
				array ('next' => 1)
			)
		);

		if (!$this->request->input ('next')) {
			$loginUrl = $helper->getLoginUrl($this->scopes);
			return Response::redirect ($loginUrl);
		}

		else {

			try {
				$session = $helper->getSessionFromRedirect();
			} catch(FacebookRequestException $ex) {
				// When Facebook returns an error
				return Response::error ($ex->getMessage ());
			} catch(\Exception $ex) {
				// When validation fails or other local issues
				return Response::error ($ex->getMessage ());
			}

			if ($session) {

				// Check if this user is already registered.
				$request = new FacebookRequest($session, 'GET', '/me', array ('fields' => 'id,name,gender,verified,locale,timezone,email,birthday,first_name,last_name'));
				$response = $request->execute ();
				$graphObject = $response->getGraphObject ();

				$data = $graphObject->asArray ();

				// Create an object.
				$user = new DeligatedUser ();
				$user->setType ('facebook');
				$user->setUniqueId ($data['id']);

				$user->setAccessToken ((string)$session->getAccessToken ());

				if (isset ($data['name'])) {
					$user->setName ($data['name']);
				}

				if (isset ($data['gender'])) {
					switch (strtoupper ($data['gender'])) {
						case DeligatedUser::GENDER_FEMALE:
						case DeligatedUser::GENDER_MALE:
							$user->setGender (strtoupper ($data['gender']));
						break;
					}
				}

				if (isset ($data['locale'])) {
					$user->setLocale ($data['locale']);
				}

				if (isset ($data['email'])) {
					$user->setEmail ($data['email']);
				}

				if (isset ($data['birthday'])) {
					if (strlen ($data['birthday']) == 10) {
						$parts = explode ('/', $data['birthday']);
						$user->setBirthday (Carbon::createFromDate ($parts[2], $parts[0], $parts[1]));
					}
				}

				if (isset ($data['first_name']))
					$user->setFirstname ($data['first_name']);

				if (isset ($data['last_name']))
					$user->setLastname ($data['last_name']);

				$user->setAvatar ('https://graph.facebook.com/' . $user->getUniqueId () . '/picture?type=large');

				// Touchy touchy!
				return $this->setDeligatedUser ($user);

			} else {
                return Response::error('Login failed.');
            }

		}
	}
}
