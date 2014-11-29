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

class RegistrationController {

    public function register ()
    {
        $template = new Template ('CatLab/Accounts/register.phpt');

        $template->set ('action', URLBuilder::getURL ('login/register'));
        $template->set ('email', Tools::getInput ($_POST, 'email', 'varchar'));

        return Response::template ($template);
    }

}