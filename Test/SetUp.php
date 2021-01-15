<?php


namespace LandValidator\Test;


use LandValidator\Helper;
use LandValidator\Validator;
use PHPUnit\Framework\TestCase;

class SetUp extends TestCase
{
    protected Validator $validator;
    protected Helper    $helper;

    function setUp(): void
    {
        $validator = new Validator();
        $dict      = require __DIR__ . '/../dict.php';
        $validator->useRule($dict['rule']);
        $this->validator = $validator;
        $helper          = new Helper();
        $helper->useMessage($dict['message']);
        $helper->useTrans($dict['trans']);
        $this->helper = $helper;
        parent::setUp();
    }
}