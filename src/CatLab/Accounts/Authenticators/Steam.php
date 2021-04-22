<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 30/04/15
 * Time: 1:05
 */

namespace CatLab\Accounts\Authenticators;


use CatLab\Accounts\Authenticators\Base\OpenIDAuthenticator;
use CatLab\Accounts\Models\DeligatedUser;
use Neuron\Config;
use Neuron\Net\Client;
use Neuron\Net\Request;

class Steam
	extends OpenIDAuthenticator {

    protected $trustProvidedEmailAddress = false;

	public function getOpenIDUrl () {
		return 'http://steamcommunity.com/openid';
	}

	protected function setAdditionalParameters (DeligatedUser $user) {

		$steamKey = Config::get ('accounts.steam.key');
		if ($steamKey) {

			$openid = $user->getUniqueId ();

			$params = explode ('/', $openid);
			$id = array_pop ($params);

			// Now fetch the user data
			$request = new Request ();
			$request->setUrl ('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/');
			$request->setParameters (array (
				'key' => $steamKey,
				'steamids' => $id
			));

			$response = Client::getInstance ()->get ($request);

			// No headers? No gain.
			$data = json_decode ($response->getBody (), true);

			if ($data) {

				$userdata = $data['response']['players'][0];

				$user->setName ($userdata['personaname']);
				$user->setAvatar ($userdata['avatarfull']);
				$user->setFirstname ($userdata['realname']);
				$user->setUrl ($userdata['profileurl']);

			}

		}

	}

}
