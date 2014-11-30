<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 29/11/14
 * Time: 10:49
 */

namespace CatLab\Accounts\Controllers;

use CatLab\Accounts\MapperFactory;
use Neuron\Core\Template;
use Neuron\Core\Tools;
use Neuron\Net\Response;
use Neuron\URLBuilder;

class LoginController
    extends Base
{
    public function login ()
    {
        $template = new Template ('CatLab/Accounts/login.phpt');

        if ($this->request->isPost ())
        {
            $email = $this->request->input ('email', 'email');
            $password = $this->request->input ('password');

            if ($email && $password)
            {
                $response = $this->processLogin ($email, $password);
                if ($response instanceof Response)
                {
                    return $response;
                }
                else if (is_string ($response))
                {
                    $template->set ('error', $response);
                }
            }
        }

        $template->set ('layout', $this->module->getLayout ());
        $template->set ('action', URLBuilder::getURL ($this->module->getRoutePath () . '/login'));
        $template->set ('email', $this->request->input ('email'));

        return Response::template ($template);
    }

    /**
     * Return an error (string) or redirect
     * @param $email
     * @param $password
     * @return string|Response
     */
    private function processLogin ($email, $password)
    {
        $user = MapperFactory::getUserMapper ()->getFromLogin ($email, $password);

        if ($user)
        {
            // Everything okay
            return $this->postLoginRedirect ();
        }

        else {
            // Check if we have this email address
            $user = MapperFactory::getUserMapper ()->getFromEmail ($email);
            if ($user)
            {
                return 'PASSWORD_INCORRECT';
            }
            else {
                return 'USER_NOT_FOUND';
            }
        }
    }

    private function postLoginRedirect ()
    {
        return Response::redirect (URLBuilder::getURL ('/'));
    }

    public function logout ()
    {
        $template = new Template ('CatLab/Accounts/logout.phpt');

        $template->set ('layout', $this->module->getLayout ());
        $template->set ('action', URLBuilder::getURL ($this->module->getRoutePath () . '/login'));

        return Response::template ($template);
    }
}