<?php

namespace Pagantis\ModuleUtils\Model\Log;

use Nayjest\StrCaseConverter\Str;

class LogEntry
{
    /**
     * @var string $message
     */
    protected $message;

    /**
     * @var string $code
     */
    protected $code;

    /**
     * @var string $line
     */
    protected $line;

    /**
     * @var string $file
     */
    protected $file;

    /**
     * @var string $trace
     */
    protected $trace;

    public function info($message)
    {
        $this->message = $message;

        return $this;
    }

    public function error(\Exception $exception)
    {
        $this->message = $exception->getMessage();
        $this->code    = $exception->getCode();
        $this->line    = $exception->getLine();
        $this->file    = $exception->getFile();
        $this->trace   = $exception->getTraceAsString();

        return $this;
    }

    /**
     * @return false|string
     */
    public function toJson()
    {
        $response = $this->jsonSerialize();

        return json_encode($response);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $arrayProperties = array();

        foreach ($this as $key => $value) {
            if (!empty($value)) {
                $arrayProperties[Str::toSnakeCase($key)] = $value;
            }
        }

        return $arrayProperties;
    }

    /**
     * GETTERS Y SETTERS
     */

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @param string $line
     */
    public function setLine($line)
    {
        $this->line = $line;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getTrace()
    {
        return $this->trace;
    }

    /**
     * @param string $trace
     */
    public function setTrace($trace)
    {
        $this->trace = $trace;
    }
}
