<?php

namespace LandValidator\Test;

use LandValidator\Exception\FailedList;

class ObjectTest extends SetUp
{
    protected array $data = [
        'name' => '精神小伙',
        'diff' => '一会我就没了',
        'obj1' => [
            'msg1' => 996,
            'msg2' => 'icu',
            'obj2' => [
                'msg1' => 996,
                'msg2' => 'icu',
            ]
        ]
    ];

    protected array $rules = [
        'name' => 'str',
        'obj1' => [
            'msg1' => 'int',
            'obj2' => [
                'msg1' => 'int'
            ]
        ]
    ];

    /**
     * @throws FailedList
     */
    function testSuccessObject()
    {
        $result = $this->validator->validate(
            $this->data,
            $this->rules
        );
        $this->assertEquals([
            'name' => '精神小伙',
            'obj1' => [
                'msg1' => 996,
                'obj2' => [
                    'msg1' => 996,
                ]
            ]
        ], $result);
    }
}