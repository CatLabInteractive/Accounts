<?php

namespace CatLab\Accounts\Authenticators;

use CatLab\Accounts\Authenticators\Base\Authenticator;
use CatLab\Accounts\Controllers\Base;
use CatLab\Accounts\Enums\Errors;
use CatLab\Accounts\Exceptions\AlreadyHasActiveRecoveryRequest;
use CatLab\Accounts\Mappers\UserMapper;
use Neuron\Exceptions\ExpectedType;
use Neuron\MapperFactory;
use CatLab\Accounts\Models\User;
use Neuron\Core\Template;
use Neuron\Core\Tools;
use Neuron\Net\Client;
use Neuron\Net\Request;
use Neuron\Net\Response;
use Neuron\Tools\Text;
use Neuron\Tools\TokenGenerator;
use Neuron\URLBuilder;

/**
 * Class Password
 * @package CatLab\Accounts\Authenticators
 */
class Password extends Authenticator
{
    /**
     * @var string
     */
    private $recaptchaClientKey;

    /**
     * @var string
     */
    private $recaptchaClientSecret;

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
     * @return bool|Response|string
     * @throws ExpectedType
     * @throws \Neuron\Exceptions\DataNotSet
     * @throws \Neuron\Exceptions\InvalidParameter
     */
    public function login()
    {
        if (!$this->module->getRateLimiter()->attemptIpRateLimit('login')) {
            return Response::error(Errors::LOGIN_RATE_EXCEEDED, 429);
        }

        // Check for lost password form
        if ($this->request->input('lostPassword')) {
            return $this->lostPassword();
        }

        $validCsfr = Base::isValidCsfrToken($this->request);
        $template = $this->getLoginForm('CatLab/Accounts/authenticators/password/page.phpt');

        if ($this->request->isPost()) {
            if (!$validCsfr) {
                $template->set('error', 'Invalid request, please try again.');
            } else {
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
        }

        return Response::template($template);
    }

    /**
     * @return Response
     * @throws ExpectedType
     */
    public function lostPassword()
    {
        $step = $this->request->input('lostPassword');

        switch ($step) {

            case 2:
                return $this->getChangePasswordForm();

            case 1:
            default:
                return $this->getLostPasswordForm();
        }
    }

    /**
     * @return Response
     * @throws ExpectedType
     */
    private function getLostPasswordForm()
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
            if (!Base::isValidCsfrToken($this->request)) {
                $template->set('error', 'Invalid request, please try again.');
            } else {
                $email = $this->request->input('email', 'email');

                $response = $this->processRecoverPassword($email);
                if ($response instanceof Response) {
                    return $response;
                } else if (is_string($response)) {
                    $template->set('error', $response);
                }
            }
        }

        $template->set('csfr', Base::generateCsfrToken($this->request));
        return Response::template($template);
    }

    /**
     * @return Response
     * @throws \Neuron\Exceptions\DataNotSet
     */
    private function getChangePasswordForm()
    {
        $id = $this->request->input('id');
        $token = $this->request->input('token');

        $passwordRecovery = \CatLab\Accounts\MapperFactory::getPasswordRecoveryMapper()->getFromId($id);
        if ($passwordRecovery) {
            if (
                $passwordRecovery->isExpired() ||
                $passwordRecovery->getToken() !== $token
            ) {
                $passwordRecovery = null;
            }
        }

        if (!$passwordRecovery) {
            return Response::error(
                Text::get('The password recovery link you have clicked is not valid.'),
                Response::STATUS_NOTFOUND
            );
        }

        // Password recovery token is valid.
        $template = new Template('CatLab/Accounts/authenticators/password/page.phpt');
        $template->set('layout', $this->module->getLayout());
        $template->set('formTemplate', 'CatLab/Accounts/authenticators/password/changePassword.phpt');
        $template->set('action', URLBuilder::getURL(
            $this->module->getRoutePath() . '/login/password',
            array(
                'lostPassword' => 2,
                'id' => $id,
                'token' => $token
            )
        ));

        $user = $passwordRecovery->getUser();
        $template->set('user', $user);

        if ($this->request->isPost()) {
            $password = $this->request->input('password', 'password');
            $confirmPassword = $this->request->input('password_confirmation', 'password');

            // Email address can be verified, as this link was signed.
            $user->setEmailVerified(true);

            $response = $this->processChangePassword($user, $password, $confirmPassword);
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
     * @throws \Neuron\Exceptions\DataNotSet
     * @throws \Neuron\Exceptions\InvalidParameter
     */
    public function register()
    {
        $template = new Template ('CatLab/Accounts/authenticators/password/register.phpt');

        $receivedToken = $this->request->input('token');
        if (
            $this->request->isPost() &&
            $receivedToken
        ) {
            $email = $this->request->input('email', 'email');
            $password = $this->request->input('password');

            $firstName = $this->request->input('firstName', 'text');
            $lastName = $this->request->input('lastName', 'text');

            $birthDate = $this->request->input('birthdate', 'date');
            if ($birthDate) {
                $birthDate = new \DateTime(date('Y-m-d', $birthDate));
            }

            $response = $this->processRegister($email, $password, $firstName, $lastName, $birthDate);
            if ($response instanceof Response) {
                return $response;
            } else if (is_string($response)) {
                $template->set('error', $response);
            }
        }

        // Set form protection
        $formToken = TokenGenerator::getToken(32);
        $this->request->getSession()->set('formToken', $formToken);

        if ($this->recaptchaClientKey) {
            $template->set('recaptchaClientKey', $this->recaptchaClientKey);
        }

        $template->set('token', $formToken);
        $template->set('layout', $this->module->getLayout());
        $template->set('action', URLBuilder::getURL($this->module->getRoutePath() . '/register/' . $this->getToken()));
        $template->set('email', $this->request->input('email', 'string'));
        $template->set('firstName', $this->request->input('firstName', 'string'));
        $template->set('lastName', $this->request->input('lastName', 'string'));
        $template->set('module', $this->module);

        $this->addOtherAuthenticators($template);

        return Response::template($template);
    }

    /**
     * Return an error (string) or redirect
     * @param $email
     * @param $password
     * @return string|Response
     * @throws ExpectedType
     * @throws \Neuron\Exceptions\DataNotSet
     */
    private function processLogin($email, $password)
    {
        /** @var UserMapper $mapper */
        $mapper = MapperFactory::getUserMapper();
        ExpectedType::check($mapper, UserMapper::class);

        // Check if we have this email address
        $user = $mapper->getFromEmail($email);
        if (!$user) {
            return Errors::USER_NOT_FOUND;
        }

        if (!$this->module->getRateLimiter()->attemptLogin($user)) {
            return Errors::LOGIN_RATE_EXCEEDED;
        }

        $user = $mapper->getFromLogin($email, $password);

        if ($user) {
            // Everything okay
            return $this->module->login($this->request, $user);
        } else {
            // Check if we have this email address
            $user = $mapper->getFromEmail($email);
            if ($user) {
                return Errors::PASSWORD_INCORRECT;
            } else {
                return Errors::USER_NOT_FOUND;
            }
        }
    }

    /**
     * @param $email
     * @param $password
     * @param $firstName
     * @param $lastName
     * @param \DateTime|null $birthDate
     * @return bool|string
     * @throws ExpectedType
     * @throws \Neuron\Exceptions\DataNotSet
     * @throws \Neuron\Exceptions\InvalidParameter
     */
    private function processRegister($email, $password, $firstName, $lastName, \DateTime $birthDate = null)
    {
        /** @var UserMapper $mapper */
        $mapper = MapperFactory::getUserMapper();
        ExpectedType::check($mapper, UserMapper::class);

        // Check for token
        $token = $this->request->getSession()->get('formToken');
        $receivedToken = $this->request->input('token');
        if (!$token || !$receivedToken || $token !== $receivedToken) {
            return Errors::INVALID_REQUEST;
        }

        // Verify recaptcha
        if (!$this->verifyRecaptcha()) {
            return Errors::INVALID_REQUEST;
        }

        // Check email invalid
        if (!$email) {
            return Errors::EMAIL_INVALID;
        }

        // Check if password is good
        if (!Tools::checkInput($password, 'password')) {
            return Errors::PASSWORD_INVALID;
        }

        // Check if email is unique
        $user = $mapper->getFromEmail($email);
        if ($user) {
            return Errors::EMAIL_DUPLICATE;
        }

        if (!$birthDate) {
            return Errors::BIRTHDATE_INVALID;
        }

        // Check for age lock
        $ageWallResponse = $this->module->checkAgeGate($birthDate);
        if ($ageWallResponse !== true) {
            return $ageWallResponse;
        }

        // Create the user
        $user = new User ();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setFamilyName($lastName);
        $user->setPassword($password);
        $user->setBirthDate($birthDate);

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
     * @throws ExpectedType
     */
    private function processRecoverPassword($email)
    {
        /** @var UserMapper $mapper */
        $mapper = MapperFactory::getUserMapper();
        ExpectedType::check($mapper, UserMapper::class);

        // Check email invalid
        if (!$email) {
            return Errors::EMAIL_INVALID;
        }

        $template = new Template('CatLab/Accounts/authenticators/password/page.phpt');
        $template->set('layout', $this->module->getLayout());
        $template->set('formTemplate', 'CatLab/Accounts/authenticators/password/passwordSent.phpt');
        $template->set('login', URLBuilder::getURL(
            $this->module->getRoutePath() . '/login/' . $this->getToken())
        );

        $user = $mapper->getFromEmail($email);
        if ($user) {
            try {
                $user->generatePasswordRecoveryEmail($this->module);
            } catch (AlreadyHasActiveRecoveryRequest $e) {
                // Do not show an error, as this might disclose private information.
                return Response::template($template);
            }
        }

        return Response::template($template);
    }

    /**
     * Change the password of a user and sent them on their way.
     * @param User $user
     * @param $password
     * @param $confirmPassword
     * @return string
     * @throws \Neuron\Exceptions\DataNotSet
     */
    private function processChangePassword(User $user, $password, $confirmPassword)
    {
        if (empty($password)) {
            return Errors::PASSWORD_INVALID;
        }

        if ($password !== $confirmPassword) {
            return Errors::CONFIRM_PASSWORD_INVALID;
        }

        $user->changePassword($this->module, $password);

        // Now login this user.
        return $this->module->login($this->request, $user);
    }

    /**
     * @return Template
     */
    public function getInlineForm()
    {
        $template = new Template ('CatLab/Accounts/authenticators/password/inlineform.phpt');

        $template->set('action', URLBuilder::getURL($this->module->getRoutePath() . '/login/password', array('return' => $this->request->getUrl())));
        $template->set('email', Tools::getInput($_POST, 'email', 'varchar'));
        $template->set('csfr', Base::generateCsfrToken($this->request));

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
            URLBuilder::getURL(
                $this->module->getRoutePath() . '/login/' . $this->getToken(),
                array(
                    'lostPassword' => 1
                )
            )
        );

        $template->set('email', $this->request->input('email'));
        $template->set('layout', $this->module->getLayout());
        $template->set('formTemplate', 'CatLab/Accounts/authenticators/password/form.phpt');
        $template->set('csfr', Base::generateCsfrToken($this->request));

        $this->addOtherAuthenticators($template);

        return $template;
    }

    /**
     * @param string $clientKey
     * @param string $clientSecret
     * @return Password
     */
    public function setReCaptcha($clientKey, $clientSecret)
    {
        $this->recaptchaClientKey = $clientKey;
        $this->recaptchaClientSecret = $clientSecret;

        return $this;
    }

    /**
     * Verify recaptcha code
     */
    private function verifyRecaptcha()
    {
        if (!isset($this->recaptchaClientKey)) {
            return true;
        }

        $recaptcha = $this->request->input('g-recaptcha-response');
        if (!$recaptcha) {
            return false;
        }

        $request = new Request();
        $request->setUrl('https://www.google.com/recaptcha/api/siteverify');
        $request->setBody([
            'secret' => $this->recaptchaClientSecret,
            'response' => $recaptcha,
            'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null
        ]);

        $client = Client::getInstance();

        $response = $client->post($request);
        if (!$response->getBody()) {
            return false;
        }

        $responseData = json_decode($response->getBody(), true);
        return isset($responseData['success']) && $responseData['success'];
    }

    /**
     * @param Template $template
     */
    private function addOtherAuthenticators(Template $template)
    {
        $authenticators = $this->module->getAuthenticators ();
        $otherAuthenticators = [];
        foreach ($authenticators as $v) {
            if (!($v instanceof self)) {
                $v->setRequest($this->request);
                $otherAuthenticators[] = $v;
            }
        }

        $template->set ('otherAuthenticators', $otherAuthenticators, true);
    }

}
