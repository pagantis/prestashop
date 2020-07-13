<?php

namespace Pagantis\OrdersApiClient\Method;

/**
 * Interface MethodInterface
 * @package Pagantis\OrdersApiClient\Method
 */
interface MethodInterface
{
    /**
     * All Api Methods should implement the function call
     *
     * @return AbstractMethod
     */
    public function call();
}
