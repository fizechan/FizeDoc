<?php

namespace driver;

use fize\doc\driver\Markdown;
use PHPUnit\Framework\TestCase;

class TestMarkdown extends TestCase
{

    public function testModifyQuote()
    {

    }

    public function testModify()
    {

    }



    public function testModifyEmphasis()
    {

    }

    public function testOriginal()
    {

    }

    public function testTitle()
    {

    }

    public function testBlock()
    {

    }

    public function testTable()
    {
        $rows = [
            [
                'name' => '香蕉',
                'price' => '$1',
                'count' => '5'
            ],
            [
                'name' => '平果',
                'price' => '$12',
                'count' => '16'
            ],
            [
                'name' => '草莓',
                'price' => '$21',
                'count' => '60'
            ],
            [
                'name' => '火龙果',
                'price' => '$21',
                'count' => '12'
            ]
        ];
        $headers = [
            'name' => '水果',
            'price' => '价格',
            'count' => '数量'
        ];

        $str = Markdown::table($rows, $headers);
        self::assertIsString($str);
        echo $str;
    }

    public function testLink()
    {
        $str = Markdown::link("这是个链接", 'http://www.baidu.com');
        self::assertIsString($str);
        echo $str;
    }

    public function testField()
    {
        $str = Markdown::field("变量", "这个是变量说明");
        self::assertIsString($str);
        echo $str;
    }
}
