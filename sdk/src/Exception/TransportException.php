<?php

namespace Teamgate\Exception;

class TransportException extends \Exception
{
    public $output = '';
    
    public function __construct($message, $code = 0, $output = '')
    {
        $this->output = $output;
        parent::__construct($message, $code);
    }
}
