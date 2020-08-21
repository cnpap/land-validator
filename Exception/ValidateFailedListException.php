<?php

namespace LandValidator\Exception;

use RuntimeException;
use Throwable;

class ValidateFailedListException extends \Exception
{
    public array $exceptions;

    public function __construct(array $exceptions)
    {
        $this->exceptions = $exceptions;
        parent::__construct();
    }
}