<?php

namespace LandValidator\Exception;

use RuntimeException;

class ValidateFailedException extends RuntimeException
{
    public string $path;

    public string $name;

    public array $params;

    function __construct(string $path, string $name, array $params = [])
    {
        $this->path = $path;
        $this->name = $name;
        $this->params = $params;
        parent::__construct();
    }
}