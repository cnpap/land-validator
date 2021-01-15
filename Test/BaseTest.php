<?php

namespace LandValidator\Test;

use LandValidator\Exception\Invalid;
use LandValidator\Exception\Undefined;
use LandValidator\Exception\Failed;
use LandValidator\Exception\FailedList;
use LandValidator\Validator;

class BaseTest extends SetUp
{
    private array $data = [
        'name' => 'bala' . 'bala' . '小魔仙',
        'memo' => '中二少年',
    ];

    private array $rules = [
        'name' => 'str&&strBtw:50,100',
        'memo' => 'str&&strBtw:50,100'
    ];

    /**
     * @throws FailedList
     */
    function testExample()
    {
        $this->expectNotToPerformAssertions();
        $this->validator->validate([
            'name'  => '渣渣辉',
            'roles' => [
                '财务',
                '设计',
                '程序',
                '美术',
                '运营'
            ]
        ], [
            'name'    => 'str&&strBtw:4,20',
            'roles'   => 'arr&&arrBtw:1,5',
            'roles.*' => 'str&&strBtw:4,20'
        ]);

    }

    /**
     * @throws FailedList| Failed
     */
    function testAllLevel()
    {
        try {
            $this->validator->validate($this->data, $this->rules, Validator::LEVEL1);
        } catch (Failed $e) {
            $level1Result = $this->helper->fmt($e);
            $this->assertEquals([
                'name' => '名称 应该介于 50 - 100 个字符'
            ], $level1Result);
        }
        try {
            $this->validator->validate($this->data, $this->rules, Validator::LEVEL2);
        } catch (FailedList $e) {
            $level2Result = $this->helper->fmtList($e);
            $this->assertEquals([
                'name' => '名称 应该介于 50 - 100 个字符',
                'memo' => '备注 应该介于 50 - 100 个字符'
            ], $level2Result);
        }
    }

    /**
     * @throws FailedList
     */
    function testUndefined()
    {
        $this->expectException(Undefined::class);
        $this->validator->validate($this->data, [
            'name' => 'no_exists_method'
        ]);
    }

    /**
     * @throws FailedList
     */
    function testInvalid()
    {
        $this->expectException(Invalid::class);
        $this->validator->validate($this->data, [
            'name'   => 'str',
            'name.*' => 'str'
        ]);
    }

    /**
     * @throws FailedList
     */
    function testSuccess()
    {
        $result = $this->validator->validate($this->data, [
            'name' => 'str',
            'memo' => 'str'
        ]);
        $this->assertEquals($this->data, $result);
    }

    /**
     * @throws FailedList
     */
    function testSuccessDiff()
    {
        $result = $this->validator->validate($this->data, [
            'name' => 'str',
        ]);
        $this->assertEquals([
            'name' => $this->data['name']
        ], $result);
    }
}