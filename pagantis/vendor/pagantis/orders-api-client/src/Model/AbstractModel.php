<?php

namespace Pagantis\OrdersApiClient\Model;

use Nayjest\StrCaseConverter\Str;

/**
 * Class AbstractModel
 *
 * @package Pagantis\OrdersApiClient\Model
 */
abstract class AbstractModel implements ModelInterface
{
    /**
     * Export as Array the object recursively
     *
     * @param bool $validation
     *
     * @return \stdClass
     */
    public function export($validation = true)
    {
        $result = new \StdClass();
        foreach ($this as $key => $value) {
            if (!is_null($value)) {
                $result->{Str::toSnakeCase($key)} = $this->parseValue($value, $validation);
            }
        }

        return $result;
    }

    /**
     * Parse the value of the object depending of type.
     *
     * @param $value
     * @param $validation
     *
     * @return array|string
     */
    protected function parseValue($value, $validation)
    {
        if (is_array($value) && !empty($value)) {
            $valueArray = array();
            foreach ($value as $subKey => $subValue) {
                if (is_object($subValue) && $subValue instanceof AbstractModel) {
                    $valueArray[Str::toSnakeCase($subKey)] = $subValue->export($validation);
                } else {
                    $valueArray[Str::toSnakeCase($subKey)] = $subValue;
                }
            }
            return $valueArray;
        }
        if (is_object($value) && $value instanceof AbstractModel && !empty($value)) {
            return $value->export();
        }
        if (is_object($value) && $value instanceof \DateTime && !empty($value)) {
            return $value->format('Y-m-d\Th:i:s');
        }

        return $value;
    }

    /**
     * Fill Up the Order from the json_decode(false) result of the API response.
     *
     * @param \stdClass $object
     *
     * @throws \Exception
     */
    public function import($object)
    {
        if (is_object($object)) {
            $properties = get_object_vars($object);
            foreach ($properties as $key => $value) {
                if (property_exists($this, lcfirst(Str::toCamelCase($key)))) {
                    if (is_object($value)) {
                        $objectProperty = $this->{lcfirst(Str::toCamelCase($key))};
                        if ($objectProperty instanceof AbstractModel) {
                            $objectProperty->import($value);
                        }
                    } else {
                        if (is_string($value) && $this->validateDate($value)) {
                            $this->{lcfirst(Str::toCamelCase($key))} = new \DateTime($value);
                        } else {
                            $this->{lcfirst(Str::toCamelCase($key))} = $value;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $date
     *
     * @return bool
     */
    private function validateDate($date)
    {
        try {
            $dateTime = new \DateTime($date);
        } catch (\Exception $exception) {
            return false;
        }

        if ($dateTime && substr($dateTime->format('c'), 0, 19) === substr($date, 0, 19)) {
            return true;
        }

        return false;
    }

    /**
     * @param null $date
     * @return null | String
     */
    protected function checkDateFormat($date = null)
    {
        if (empty($date) || $date == '0000-00-00') {
            return null;
        }
        if ($date instanceof \DateTime) {
            return $date->format('Y-m-d');
        }
        try {
            $dateTime = new \DateTime(trim($date));
            $today = new \DateTime('today');
            if ($dateTime >= $today) {
                return null;
            }
            return $dateTime->format('Y-m-d');
        } catch (\Exception $exception) {
            return null;
        }
    }

}
