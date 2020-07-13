<?php

namespace Pagantis\OrdersApiClient\Model\Order;

use Pagantis\OrdersApiClient\Model\AbstractModel;

/**
 * Class Metadata
 * @package Pagantis\OrdersApiClient\Model\Order
 */
class Metadata extends AbstractModel
{
    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function addMetadata($key, $value)
    {
        $this->{$key} = $value;

        return $this;
    }

    /**
     * @param $object
     *
     */
    public function import($object)
    {
        foreach ($object as $key => $value) {
            $this->addMetadata($key, $value);
        }
    }

    /**
     * Metadata will not use Str::ToCamelCase because we respect the merchant naming convention.
     *
     * @param bool $validation
     *
     * @return \stdClass
     */
    public function export($validation = true)
    {
        $result = new \stdClass();
        foreach ($this as $key => $value) {
            if ($value) {
                $result->{$key} = $value;
            }
        }

        return $result;
    }
}
