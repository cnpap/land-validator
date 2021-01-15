<?php

namespace LandValidator\Exception;

use Exception;

class FailedList extends Exception
{
    public array  $exceptions = [];
    public string $prefix     = '';

    public function __construct(array $exceptions, string $prefix = '')
    {
        $this->exceptions = $exceptions;
        $this->prefix     = $prefix;
        parent::__construct();
    }
}