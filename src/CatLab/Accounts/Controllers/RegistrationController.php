<?php

namespace CatLab\Accounts\Controllers;

use Neuron\Core\Template;
use Neuron\Core\Tools;
use Neuron\Net\Response;
use Neuron\URLBuilder;

class RegistrationController
    extends Base {

    /**
     * @return Response
     * @throws \Neuron\Exceptions\DataNotSet
     */
    public function register ()
    {
        $this->module->setPostLoginRedirects($this->request);

        // Check if already registered
        if ($user = $this->request->getUser ())
            return $this->module->postLogin($this->request, $user);

        $authenticators = $this->module->getAuthenticators ();
        $authenticator = $authenticators[0]->getToken ();

        //return Response::redirect(URLBuilder::getURL ($this->module->getRoutePath () . '/register/' . $authenticator));
        return $this->authenticator($authenticator);
    }

    /**
     * @param $token
     * @return Response
     */
    public function authenticator ($token)
    {
        $authenticator = $this->module->getAuthenticators ()->getFromToken ($token);

        if (!$authenticator)
        {
            return Response::error ('Authenticator not found', Response::STATUS_NOTFOUND);
        }

        $authenticator->setRequest ($this->request);

        return $authenticator->register ();
    }

}
