<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 29/11/14
 * Time: 10:49
 */

namespace CatLab\Signin\Controllers;

use Neuron\Core\Template;
use Neuron\Net\Response;
use Neuron\URLBuilder;

class LoginController {

    public function login ()
    {
        $template = new Template ('CatLab/Signin/login.phpt');

        $template->set ('action', URLBuilder::getURL ('login/login'));

        return Response::template ($template);
    }

}