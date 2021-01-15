<?php

namespace LandValidator\Test;

use LandValidator\Exception\Failed;
use LandValidator\Exception\FailedList;
use LandValidator\Validator;

class ArrTest extends SetUp
{
    protected array $data = [
        'name' => '精神小伙',
        'diff' => '一会我就没了',
        'arr1' => [
            [
                'msg1' => 996,
                'msg2' => 'icu',
                'msg3' => '上班使我快乐'
            ],
            [
                'msg1' => 996,
                'msg2' => 'icu',
                'msg3' => '上班使我快乐'
            ]
        ]
    ];

    protected array $rules = [
        'name'   => 'str',
        'arr1'   => 'arr',
        'arr1.*' => [
            'msg2' => 'int',
            'msg3' => 'int'
        ]
    ];

    /**
     * @throws FailedList
     */
    function testAllLevel()
    {
        try {
            $this->validator->validate(
                $this->data,
                $this->rules,
                Validator::LEVEL1
            );
        } catch (Failed $e) {
            $this->assertEquals([
                'arr1.*.msg2' => 'arr1.*.msg2 应该是整数'
            ], $this->helper->fmt($e));
        }
        try {
            $this->validator->validate(
                $this->data,
                $this->rules
            );
        } catch (FailedList $e) {
            $this->assertEquals([
                'arr1.*.msg2' => 'arr1.*.msg2 应该是整数',
                'arr1.*.msg3' => 'arr1.*.msg3 应该是整数'
            ], $this->helper->fmtList($e));
        }
    }

    function testArrMax()
    {
        try {
            $this->validator->validate(
                $this->data,
                [
                    'arr1' => 'arr&&arrMax:1'
                ]
            );
        } catch (FailedList $e) {
            $this->assertEquals([
                'arr1' => 'arr1 不能多于 1 个元素'
            ], $this->helper->fmtList($e));
        }
    }
}