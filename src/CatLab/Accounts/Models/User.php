<?php

namespace CatLab\Accounts\Models;

use CatLab\Accounts\Exceptions\AlreadyHasActiveRecoveryRequest;
use CatLab\Accounts\MapperFactory;
use CatLab\Accounts\Module;
use CatLab\Mailer\Mailer;
use CatLab\Mailer\Models\Mail;
use DateTime;
use Neuron\Collections\Collection;
use Neuron\Config;
use Neuron\Core\Template;
use Neuron\Tools\Text;
use Neuron\Tools\TokenGenerator;
use Neuron\URLBuilder;

/**
 * Class User
 * @package CatLab\Accounts\Models
 */
class User implements \Neuron\Interfaces\Models\User
{

    /**
     * @var int $id
     */
    private $id;

    /**
     * @var string $email
     */
    private $email;

    /**
     * @var string $password
     */
    private $password;

    /**
     * @var string $passwordhash
     */
    private $passwordhash;

    /**
     * @var boolean
     */
    private $emailVerified;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $familyName;

    /**
     * @var DateTime
     */
    private $birthDate;

    /**
     * @var DateTime
     */
    private $anonymizedAt;

    /**
     * @var DateTime
     */
    private $createdAt;

    /**
     * @var DateTime
     */
    private $updatedAt;

    public function __construct()
    {

    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param string $hash
     */
    public function setPasswordHash($hash)
    {
        $this->passwordhash = $hash;
    }

    /**
     * @return string
     */
    public function getPasswordHash()
    {
        return $this->passwordhash;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * @param string $familyName
     * @return User
     */
    public function setFamilyName($familyName)
    {
        $this->familyName = $familyName;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @param DateTime $birthDate
     * @return User
     */
    public function setBirthDate(DateTime $birthDate)
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isEmailVerified()
    {
        return $this->emailVerified;
    }

    /**
     * @param boolean $emailVerified
     * @return self
     */
    public function setEmailVerified($emailVerified)
    {
        $this->emailVerified = $emailVerified;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getAnonymizedAt()
    {
        return $this->anonymizedAt;
    }

    /**
     * @param DateTime $anonymizedAt
     * @return User
     */
    public function setAnonymizedAt(DateTime $anonymizedAt)
    {
        $this->anonymizedAt = $anonymizedAt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAnonymized()
    {
        return $this->getAnonymizedAt() !== null;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     * @return User
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime $updatedAt
     * @return User
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }



    /**
     * @param boolean $formal If FALSE, use an informal tone (= first name only)
     * @return string
     */
    public function getDisplayName($formal = false)
    {
        if ($formal && $this->getFirstName() && $this->getFamilyName()) {
            return $this->getFirstName() . ' ' . $this->getFamilyName();
        } elseif ($this->getFirstName()) {
            return $this->getFirstName();
        } elseif ($this->getFamilyName()) {
            return $this->getFamilyName();
        } else {
            return 'User ' . $this->getId();
        }
    }

    /**
     * @param null $type The type of user we are requesting.
     * @return Collection
     */
    public function getDeligatedAccounts($type = null)
    {
        return MapperFactory::getDeligatedMapper()->getFromUser($this, $type);
    }

    /**
     * (plan to) change the email address. The actual change only happens at
     * the moment when the user clicks the link in the verification email.
     * @param Module $module
     * @param $emailAddress
     * @throws \CatLab\Mailer\Exceptions\MailException
     */
    public function changeEmail(Module $module, $emailAddress)
    {
        if (!$this->isEmailVerified()) {
            $this->setEmail($emailAddress);
            \Neuron\MapperFactory::getUserMapper()->update($this);
        }

        $this->generateVerificationEmail($module, $emailAddress);
    }

    /**
     * @param Module $module
     * @param $password
     */
    public function changePassword(Module $module, $password)
    {
        $this->setPassword($password);
        \Neuron\MapperFactory::getUserMapper()->update($this);

        $this->onPasswordChanged();
    }

    /**
     * @param Module $module
     * @param string $emailAddress
     * @throws \CatLab\Mailer\Exceptions\MailException
     */
    public function generateVerificationEmail(Module $module, $emailAddress)
    {
        $email = new Email ();
        $email->setEmail($emailAddress);
        $email->setExpires(new DateTime ('next week'));
        $email->setToken(TokenGenerator::getSimplifiedToken(24));
        $email->setUser($this);
        $email->setVerified(false);

        MapperFactory::getEmailMapper()->create($email);

        $this->sendVerificationEmail($module, $email->getEmail(), $email->getVerifyURL($module->getRoutePath()));
    }

    /**
     * @param Module $module
     * @param string $emailAddress
     * @param $verifyUrl
     * @throws \CatLab\Mailer\Exceptions\MailException
     */
    public function sendVerificationEmail(Module $module, $emailAddress, $verifyUrl)
    {
        Text::getInstance()->setDomain('catlab.accounts');

        $template = new Template ('CatLab/Accounts/mails/verification.phpt');
        $template->set('user', $this);
        $template->set('verify_url', $verifyUrl);

        $mail = new Mail ();
        $mail->setSubject(Text::getInstance()->getText('Email address verification'));
        $mail->setTemplate($template);
        $mail->getTo()->add($emailAddress);
        $mail->setFrom(Config::get('mailer.from.email'));

        Mailer::getInstance()->send($mail);
    }

    /**
     * @param Module $module
     * @throws \CatLab\Mailer\Exceptions\MailException
     */
    public function sendConfirmationEmail(Module $module)
    {
        Text::getInstance()->setDomain('catlab.accounts');

        $template = new Template ('CatLab/Accounts/mails/confirmation.phpt');
        $template->set('user', $this);

        $mail = new Mail ();
        $mail->setSubject(Text::getInstance()->gettext('Account creation'));
        $mail->setTemplate($template);
        $mail->getTo()->add($this->getEmail());
        $mail->setFrom(Config::get('mailer.from.email'));

        Mailer::getInstance()->send($mail);
    }

    /**
     * Generate and send a password recovery request.
     * @param Module $module
     * @return bool
     * @throws AlreadyHasActiveRecoveryRequest
     * @throws \CatLab\Mailer\Exceptions\MailException
     */
    public function generatePasswordRecoveryEmail(Module $module)
    {
        // Do we have a recent password recovery request?
        if (MapperFactory::getPasswordRecoveryMapper()->hasRecentActiveRecoveryRequest($this)) {
            throw new AlreadyHasActiveRecoveryRequest('We already have a pending recover password request for this account. Please try again in 24 hours.');
        }

        $expirationDate = new DateTime();
        $expirationDate->add(new DateInterval('PT1H'));

        $passwordRecoveryRequest = new PasswordRecovery();
        $passwordRecoveryRequest->setExpires($expirationDate);
        $passwordRecoveryRequest->setToken(TokenGenerator::getSimplifiedToken(24));
        $passwordRecoveryRequest->setUser($this);

        MapperFactory::getPasswordRecoveryMapper()->create($passwordRecoveryRequest);

        $this->sendPasswordRecoveryEmail($module, $passwordRecoveryRequest->getUrl($module->getRoutePath()));

        return true;
    }

    /**
     * Send out the actual password recovery email.
     * @param Module $module
     * @param $recoveryUrl
     * @throws \CatLab\Mailer\Exceptions\MailException
     */
    public function sendPasswordRecoveryEmail(Module $module, $recoveryUrl)
    {
        Text::getInstance()->setDomain('catlab.accounts');

        $template = new Template('CatLab/Accounts/mails/passwordRecovery.phpt');
        $template->set('user', $this);
        $template->set('recovery_url', $recoveryUrl);

        $mail = new Mail ();
        $mail->setSubject(Text::getInstance()->getText('Password recovery'));
        $mail->setTemplate($template);
        $mail->getTo()->add($this->getEmail());
        $mail->setFrom(Config::get('mailer.from.email'));

        Mailer::getInstance()->send($mail);
    }

    /**
     * Called when email address was changed.
     */
    public function onEmailAddressChanged()
    {

    }

    /**
     * Called when password was changed.
     */
    public function onPasswordChanged()
    {

    }

    /**
     * @return string
     */
    public function getTrackingId()
    {
        return md5($this->getId());
    }

    /**
     * @return string[]
     */
    public function getTrackingData()
    {
        return [
            'user_id' => $this->getTrackingId(),
        ];
    }
}
