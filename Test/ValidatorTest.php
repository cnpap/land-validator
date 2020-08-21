<?php

namespace LandValidator\Test;

use LandValidator\Exception\NotExpectedDataFormatException;
use LandValidator\Exception\NoRuleMethodException;
use LandValidator\Exception\ValidateFailedException;
use LandValidator\Exception\ValidateFailedListException;
use LandValidator\Helper;
use LandValidator\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    private array $data = [
        'name' => 'bala' . 'bala' . '小魔仙',
        'memo' => '中二少年'
    ];

    private array $rules = [
        'name' => 'str&&strBtw:50,100',
        'memo' => 'str&&strBtw:50,100'
    ];

    private Validator $validator;

    private Helper $helper;

    function setUp(): void
    {
        $validator = new Validator();
        $validator->useRule(require __DIR__ . '/../rule.php');
        $this->validator = $validator;
        $helper = new Helper();
        $helper->useMessage(require __DIR__ . '/../message.php');
        $helper->useTransName(require __DIR__ . '/../transName.php');
        $this->helper = $helper;
        parent::setUp();
    }

    /**
     * @throws ValidateFailedListException
     */
    function testExample()
    {
        $this->expectNotToPerformAssertions();
        $this->validator->validate([
            'name' => '渣渣辉',
            'roles' => [
                '财务',
                '设计',
                '程序',
                '美术',
                '运营'
            ]
        ], [
            'name' => 'str&&strBtw:4,20',
            'roles' => 'arr&&arrBtw:1,5',
            'roles.*' => 'str&&strBtw:4,20'
        ]);

    }

    /**
     * @throws ValidateFailedListException| ValidateFailedException
     */
    function testAllLevel()
    {
        try {
            $this->validator->validate($this->data, $this->rules, Validator::LEVEL1);
        } catch (ValidateFailedException $e) {
            $level1Result = $this->helper->fmtFailed($e);
            $this->assertEquals([
                'name' => '名称 应该介于 50 - 100 个字符'
            ], $level1Result);
        }
        try {
            $this->validator->validate($this->data, $this->rules, Validator::LEVEL2);
        } catch (ValidateFailedListException $e) {
            $level2Result = $this->helper->fmtFailedList($e);
            $this->assertEquals([
                'name' => '名称 应该介于 50 - 100 个字符',
                'memo' => '备注 应该介于 50 - 100 个字符'
            ], $level2Result);
        }
    }

    /**
     * @throws ValidateFailedListException
     */
    function testNoRuleMethodException()
    {
        $this->expectException(NoRuleMethodException::class);
        $this->validator->validate($this->data, [
            'name' => 'no_exists_method'
        ]);
    }

    /**
     * @throws ValidateFailedListException
     */
    function testNotExpectedDataFormatException()
    {
        $this->expectException(NotExpectedDataFormatException::class);
        $this->validator->validate($this->data, [
            'name' => 'str',
            'name.*' => 'str'
        ]);
    }
}