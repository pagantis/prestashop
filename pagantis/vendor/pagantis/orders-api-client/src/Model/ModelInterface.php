<?php

namespace Pagantis\OrdersApiClient\Model;

/**
 * Interface ModelInterface
 * @package Pagantis\OrdersApiClient\Model
 */
interface ModelInterface
{
    /**
     * @param bool $validation Define if we should launch or not the validation
     *
     * @return array
     */
    public function export($validation = true);

    /**
     * @param \stdClass $object
     *
     */
    public function import($object);
}
