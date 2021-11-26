<?php

namespace Tests\Driver;

use Fize\Doc\Driver\Markdown;
use PHPUnit\Framework\TestCase;

class TestMarkdown extends TestCase
{

    public function testOriginal()
    {
        $str = '#我就是我';
        $str1 = '\\#我就是我';
        $str = Markdown::original($str);
        var_dump($str);
        self::assertEquals($str1, $str);
    }

    public function testTitle()
    {
        $title = '这是标题';
        $tt = Markdown::title($title, 2);
        self::assertEquals('## ' . $title. "\r\n", $tt);
    }

    public function testModify()
    {
        $str1 = '这是一段文字';
        $str2 = Markdown::modify($str1, '*');
        var_dump($str2);
        self::assertEquals('*' . $str1 . '*', $str2);
    }

    public function testModifyEmphasis()
    {
        $str1 = '这是一段文字';
        $str2 = Markdown::modifyEmphasis($str1);
        var_dump($str2);
        self::assertEquals('*' . $str1 . '*', $str2);
    }

    public function testModifyQuote()
    {
        $str1 = '这是一大段文字';
        $str2 = Markdown::modifyQuote($str1);
        var_dump($str2);
        self::assertEquals('> ' . $str1 . "\r\n\r\n", $str2);
    }

    public function testBlock()
    {
        $str1 = "这是一大段文字1\n这是一大段文字2";
        $str2 = Markdown::block($str1);
        $str3 = "    这是一大段文字1\r\n    这是一大段文字2\r\n\r\n";
        self::assertEquals($str3, $str2);
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

    public function testcode()
    {
        $code = <<<CODE
<?php
\$key = "这是一段PHP代码";
var_dump(\$key);
CODE;

        $str = Markdown::code("php", $code);
        self::assertIsString($str);
        echo $str;
    }
}
