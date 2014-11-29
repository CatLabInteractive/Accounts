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

class LoginController {

    public function login ()
    {
        $template = new Template ('CatLab/Accounts/login.phpt');

        $template->set ('action', URLBuilder::getURL ('login/login'));
        $template->set ('email', Tools::getInput ($_POST, 'email', 'varchar'));

        return Response::template ($template);
    }

    public function logout ()
    {
        $template = new Template ('CatLab/Accounts/logout.phpt');

        $template->set ('action', URLBuilder::getURL ('login/login'));

        return Response::template ($template);
    }

}