<?php

namespace CatLab\Accounts\Authenticators;

use CatLab\Accounts\Authenticators\Base\DeligatedAuthenticator;
use CatLab\Accounts\Models\DeligatedUser;
use Google_Client;
use Neuron\Config;
use Neuron\Core\Template;
use Neuron\URLBuilder;

/**
 * Class Google
 * @package CatLab\Accounts\Authenticators
 */
class Google extends DeligatedAuthenticator
{
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $secret;

    /**
     *
     */
    protected function initialize()
    {
        $this->clientId = Config::get('accounts.google.key');
        $this->secret = Config::get('accounts.google.secret');
    }

    /**
     * @return mixed|Template|string
     * @throws \Neuron\Exceptions\DataNotSet
     */
    public function getInlineForm()
    {
        $this->initialize();

        $template = new Template('CatLab/Accounts/authenticators/deligated/google/login-script.phpt');
        $template->set('clientId', $this->clientId);
        $template->set('authenticator', $this);
        $template->set('authUrl', URLBuilder::getURL($this->module->getRoutePath() . '/login/' . $this->getToken()));
        $template->set('loginButtonTemplate', 'CatLab/Accounts/authenticators/deligated/inlineform.phpt');

        return $template->parse();
    }

    /**
     * @return mixed|Template|string
     * @throws \Neuron\Exceptions\DataNotSet
     */
    public function getForm()
    {
        $this->initialize();

        $template = new Template('CatLab/Accounts/authenticators/deligated/google/login-script.phpt');
        $template->set('clientId', $this->clientId);
        $template->set('authenticator', $this);
        $template->set('authUrl', URLBuilder::getURL($this->module->getRoutePath() . '/login/' . $this->getToken()));
        $template->set('loginButtonTemplate', 'CatLab/Accounts/authenticators/deligated/form.phpt');

        return $template->parse();
    }

    public function login()
    {
        $this->initialize();

        $idToken = $this->request->input('idtoken');
        if (!$idToken) {
            return $this->getInlineForm();
        }

        $client = new Google_Client(['client_id' => $this->clientId]);  // Specify the CLIENT_ID of the app that accesses the backend
        $payload = $client->verifyIdToken($idToken);
        if ($payload) {
            $userid = $payload['sub'];
            // If request specified a G Suite domain:
            //$domain = $payload['hd'];

            $user = new DeligatedUser ();
            $user->setType ('google');
            $user->setUniqueId ($payload['sub']);

            if (isset($payload['given_name'])) {
                $user->setFirstname($payload['given_name']);
            }

            if (isset($payload['family_name'])) {
                $user->setLastname($payload['family_name']);
            }

            if (isset($payload['email'])) {
                $user->setEmail($payload['email']);
            }

            if (isset($payload['email_verified'])) {
                $this->request->getSession()->set('google-verified-email', $payload['email_verified']);
            }

            if (isset($payload['picture'])) {
                $user->setAvatar($payload['picture']);
            }

            return $this->setDeligatedUser($user);

        } else {
            return \Neuron\Net\Response::json([
                'error' => 'Invalid authentication code provided.'
            ]);
        }
    }

    /**
     * @return bool|mixed|null
     * @throws \Neuron\Exceptions\DataNotSet
     */
    protected function canTrustEmailAddress()
    {
        return $this->request->getSession()->get('google-verified-email');
    }
}
