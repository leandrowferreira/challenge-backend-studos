<?php

namespace App\Resources;

class UrlResult
{
    protected $code;
    protected $message;
    protected $result;

    public function __construct($result, $code = 200, $message = 'OK')
    {
        $this->code    = $code;
        $this->message = $message;
        $this->result  = $result ?? '';
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getResult()
    {
        return $this->result;
    }
}
