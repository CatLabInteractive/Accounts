<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 29/11/14
 * Time: 10:49
 */

namespace CatLab\Accounts\Controllers;

use Neuron\Core\Template;
use Neuron\Core\Tools;
use Neuron\Net\Response;
use Neuron\URLBuilder;

class RegistrationController
    extends Base {

    /**
     * @return Response
     */
    public function register ()
    {
	    // Check for return tag
	    if ($return = $this->request->input ('return')) {
		    $this->request->getSession ()->set ('post-login-redirect', $return);
	    }

	    // Check for cancel tag
	    if ($return = $this->request->input ('cancel')) {
		    $this->request->getSession ()->set ('cancel-login-redirect', $return);
	    }

        // Check if already registered
        if ($user = $this->request->getUser ())
            return $this->module->postLogin ($this->request, $user);

        $authenticators = $this->module->getAuthenticators ();
        $authenticator = $authenticators[0]->getToken ();

        return Response::redirect (URLBuilder::getURL ($this->module->getRoutePath () . '/register/' . $authenticator));
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