<?php

namespace CatLab\Accounts\Authenticators;

use CatLab\Accounts\Authenticators\Base\Authenticator;
use CatLab\Accounts\Mappers\UserMapper;
use Neuron\Exceptions\ExpectedType;
use Neuron\MapperFactory;
use CatLab\Accounts\Models\User;
use Neuron\Core\Template;
use Neuron\Core\Tools;
use Neuron\Net\Response;
use Neuron\URLBuilder;

/**
 * Class Password
 * @package CatLab\Accounts\Authenticators
 */
class Password extends Authenticator
{
    /**
     * @return string
     * @throws \Neuron\Exceptions\DataNotSet
     */
    public function getForm()
    {
        $template = $this->getLoginForm();
        return $template->parse();
    }

    /**
     * @return Response|string
     */
    public function login()
    {
        // Check for lost password form
        if ($this->request->input('lostPassword')) {
            return $this->lostPassword();
        }

        $template = $this->getLoginForm('CatLab/Accounts/authenticators/password/page.phpt');

        if ($this->request->isPost()) {
            $button = $this->request->input('submit');
            if ($button) {
                switch ($button) {
                    case 'register':
                        return $this->register();
                        break;
                }
            }

            $email = $this->request->input('email', 'email');
            $password = $this->request->input('password');

            if ($email && $password) {
                $response = $this->processLogin($email, $password);
                if ($response instanceof Response) {
                    return $response;
                } else if (is_string($response)) {
                    $template->set('error', $response);
                }
            }
        }

        return Response::template($template);
    }

    public function lostPassword()
    {
        $template = new Template('CatLab/Accounts/authenticators/password/page.phpt');
        $template->set('layout', $this->module->getLayout());
        $template->set('formTemplate', 'CatLab/Accounts/authenticators/password/lostPassword.phpt');

        $template->set(
            'action',
            URLBuilder::getURL
            (
                $this->module->getRoutePath() . '/login/' . $this->getToken(),
                array(
                    'lostPassword' => 1
                )
            )
        );

        $template->set('email', $this->request->input('email'));

        $template->set('login', URLBuilder::getURL(
            $this->module->getRoutePath() . '/login/' . $this->getToken())
        );

        if ($this->request->isPost()) {
            $email = $this->request->input('email', 'email');

            $response = $this->processRecoverPassword($email);
            if ($response instanceof Response) {
                return $response;
            } else if (is_string($response)) {
                $template->set('error', $response);
            }
        }

        return Response::template($template);
    }

    /**
     * @return bool|Response|string
     */
    public function register()
    {
        $template = new Template ('CatLab/Accounts/authenticators/password/register.phpt');

        if ($this->request->isPost()) {
            $email = $this->request->input('email', 'email');
            $username = $this->request->input('username', 'username');
            $password = $this->request->input('password');

            $response = $this->processRegister($email, $username, $password);
            if ($response instanceof Response) {
                return $response;
            } else if (is_string($response)) {
                $template->set('error', $response);
            }
        }

        $template->set('layout', $this->module->getLayout());
        $template->set('action', URLBuilder::getURL($this->module->getRoutePath() . '/register/' . $this->getToken()));
        $template->set('email', $this->request->input('email', 'string'));
        $template->set('username', $this->request->input('username', 'string'));

        return Response::template($template);
    }

    /**
     * Return an error (string) or redirect
     * @param $email
     * @param $password
     * @return string|Response
     */
    private function processLogin($email, $password)
    {
        $mapper = MapperFactory::getUserMapper();
        ExpectedType::check($mapper, UserMapper::class);

        $user = $mapper->getFromLogin($email, $password);

        if ($user) {
            // Everything okay
            return $this->module->login($this->request, $user);
        } else {
            // Check if we have this email address
            $user = $mapper->getFromEmail($email);
            if ($user) {
                return 'PASSWORD_INCORRECT';
            } else {
                return 'USER_NOT_FOUND';
            }
        }
    }

    /**
     * @param $email
     * @param $username
     * @param $password
     * @return bool|string
     * @throws \Neuron\Exceptions\InvalidParameter
     */
    private function processRegister($email, $username, $password)
    {
        /** @var UserMapper $mapper */
        $mapper = MapperFactory::getUserMapper();
        ExpectedType::check($mapper, UserMapper::class);

        // Check email invalid
        if (!$email) {
            return 'EMAIL_INVALID';
        }

        // Check username input
        if (!$username) {
            return 'USERNAME_INVALID';
        }

        // Check if password is good
        if (!Tools::checkInput($password, 'password')) {
            return 'PASSWORD_INVALID';
        }

        // Check if email is unique
        $user = $mapper->getFromEmail($email);
        if ($user) {
            return 'EMAIL_DUPLICATE';
        }

        // Check if username is unique
        $user = $mapper->getFromUsername($username);
        if ($user) {
            return 'USERNAME_DUPLICATE';
        }

        // Create the user
        $user = new User ();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPassword($password);

        $user = $mapper->create($user);
        if ($user) {
            return $this->module->register($this->request, $user);
        } else {
            return $mapper->getError();
        }
    }

    /**
     * @param $email
     * @return Response|string
     */
    private function processRecoverPassword($email)
    {
        /** @var UserMapper $mapper */
        $mapper = MapperFactory::getUserMapper();
        ExpectedType::check($mapper, UserMapper::class);

        // Check email invalid
        if (!$email) {
            return 'EMAIL_INVALID';
        }

        $template = new Template('CatLab/Accounts/authenticators/password/page.phpt');
        $template->set('layout', $this->module->getLayout());
        $template->set('formTemplate', 'CatLab/Accounts/authenticators/password/passwordSent.phpt');
        $template->set('login', URLBuilder::getURL(
            $this->module->getRoutePath() . '/login/' . $this->getToken())
        );

        $user = $mapper->getFromEmail($email);
        if ($user) {
            $user->sendPasswordRecoveryEmail($this->module);
        }

        return Response::template($template);
    }

    public function getInlineForm()
    {

        $template = new Template ('CatLab/Accounts/authenticators/password/inlineform.phpt');

        $template->set('action', URLBuilder::getURL($this->module->getRoutePath() . '/login/password', array('return' => $this->request->getUrl())));
        $template->set('email', Tools::getInput($_POST, 'email', 'varchar'));

        return $template;
    }

    /**
     * @param string $templatePath
     * @return Template
     */
    private function getLoginForm($templatePath = 'CatLab/Accounts/authenticators/password/form.phpt')
    {
        $template = new Template($templatePath);

        $template->set('action', URLBuilder::getURL(
            $this->module->getRoutePath() . '/login/' . $this->getToken())
        );

        $template->set('register', URLBuilder::getURL(
            $this->module->getRoutePath() . '/register/' . $this->getToken())
        );

        $template->set(
            'lostPassword',
            URLBuilder::getURL
            (
                $this->module->getRoutePath() . '/login/' . $this->getToken(),
                array(
                    'lostPassword' => 1
                )
            )
        );

        $template->set('email', $this->request->input('email'));
        $template->set('layout', $this->module->getLayout());
        $template->set('formTemplate', 'CatLab/Accounts/authenticators/password/form.phpt');

        return $template;
    }

}