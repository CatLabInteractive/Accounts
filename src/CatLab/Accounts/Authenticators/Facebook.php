<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 29/04/15
 * Time: 16:44
 */

namespace CatLab\Accounts\Authenticators;

use Carbon\Carbon;

use CatLab\Accounts\Models\DeligatedUser;

use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;

use Neuron\Config;
use Neuron\Net\Response;
use Neuron\URLBuilder;

class Facebook
	extends DeligatedAuthenticator {

	private $loaded = false;

	protected function initialize () {

		if (!$this->loaded) {
			FacebookSession::setDefaultApplication (
				Config::get ('accounts.facebook.id'),
				Config::get ('accounts.facebook.secret')
			);
			$this->loaded = true;
		}

	}

	public function login ()
	{
		$this->initialize ();

		$helper = new FacebookRedirectLoginHelper (URLBuilder::getAbsoluteURL ($this->module->getRoutePath () . '/login/' . $this->getToken (), array ('next' => 1)));

		if (!$this->request->input ('next')) {
			$loginUrl = $helper->getLoginUrl(array ('user_birthday', 'email'));
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
				$request = new FacebookRequest($session, 'GET', '/me', array ('fields' => 'id,name,gender,verified,locale,timezone,email,birthday'));
				$response = $request->execute ();
				$graphObject = $response->getGraphObject ();

				$data = $graphObject->asArray ();

				// Create an object.
				$user = new DeligatedUser ();
				$user->setType ('facebook');
				$user->setUniqueId ($data['id']);

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

				$user->setAvatar ('https://graph.facebook.com/' . $user->getUniqueId () . '/picture?type=large');

				// Touchy touchy!
				return $this->setDeligatedUser ($user);

			}

		}
	}

	public function getForm ()
	{
		return '<a href="' . URLBuilder::getURL ($this->module->getRoutePath () . '/login/facebook') . '">Login with facebook</a>';
	}
}