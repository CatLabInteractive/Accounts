<?php
/**
 * Created by PhpStorm.
 * User: daedeloth
 * Date: 11/11/14
 * Time: 17:45
 */

class FrontController
    implements \Neuron\Interfaces\FrontController
{
    /**
     * @return bool
     */
    public function canDispatch ()
    {
        // TODO: Implement canDispatch() method.
    }

    /**
     * @param \Neuron\Page $page
     * @return \Neuron\Net\Response
     */
    public function dispatch (\Neuron\Page $page)
    {
        // TODO: Implement dispatch() method.
    }

    /**
     * @param \Neuron\Interfaces\FrontController $input
     * @return mixed
     */
    public function setParentController (\Neuron\Interfaces\FrontController $input)
    {
        // TODO: Implement setParentController() method.
    }

    /**
     * @return string
     */
    public function getName ()
    {
        // TODO: Implement getName() method.
    }

    /**
     * @param null $id
     * @return string
     */
    public function getInput ($id = null)
    {
        // TODO: Implement getInput() method.
    }
}