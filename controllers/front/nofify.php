<?php

/**
 * Class PaylaterNotifyModuleFrontController
 */
class PaylaterNotifyModuleFrontController extends ModuleFrontController
{
    /**
     * Process GET request, normally working as a PING to check the url is working
     */
    public function getProcess()
    {
        $this->smartyOutputContent([
            'status' => 'ok',
            'timestamp' => time(),
        ]);
    }

    /**
     * Process POST request, normally notifications from payment gateway
     */
    public function postProcess()
    {
        $this->smartyOutputContent([
            'status' => 'ok',
            'timestamp' => time(),
        ]);
    }
}
