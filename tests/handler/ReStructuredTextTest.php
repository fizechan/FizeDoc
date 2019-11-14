<?php

namespace handler;

use fize\doc\handler\ReStructuredText;
use PHPUnit\Framework\TestCase;

class ReStructuredTextTest extends TestCase
{

    /**
     * 字符串长度，中文算2个，ascii字符算1个
     * @param string $str 字符串
     * @return int
     */
    protected static function abslength($str)
    {
        //规定占位为1的字符
        $fix1s = ['“', '”'];
        foreach ($fix1s as $fix1) {
            $str = str_replace($fix1, '*', $str);
        }
        return strlen(preg_replace("#[^\x{00}-\x{ff}]#u", '**', $str));
    }

    public function testLen()
    {
        $len = self::abslength('“');
        $len = strlen('“');
        $len = mb_strlen('“', 'GBK');
        var_dump($len);
        self::assertEquals($len, 1);
    }

    public function testParse()
    {
        ReStructuredText::register(dirname(dirname(__DIR__)) . '/src', 'fize\doc');
        $doc = new ReStructuredText('fize\doc\handler\ReStructuredText');

//        ReStructuredText::register(dirname(__DIR__) . '/data', 'fizedoc\test');
//        $doc = new ReStructuredText('fizedoc\test\subb\Test3Doc');


        $str = $doc->parse();
        echo $str;
    }

    public function testDir()
    {
        $filter = [
            'class'    => [
                'abstract'     => [false, true],
                'anonymous'    => [false, true],
                'cloneable'    => [false, true],
                'final'        => [false, true],
                'instantiable' => [false, true],
                'interface'    => [false, true],
                'internal'     => [false, true],
                'iterable'     => [false, true],
                'iterateable ' => [false, true],
                'trait'        => [false, true],
            ],
            'constant' => [
                'scope' => ['public']
            ],
            'function' => [
                'disabled'   => [false, true],
                'closure'    => [false, true],
                'deprecated' => [false, true],
                'generator'  => [false, true],
                'internal'   => [false, true],
                'variadic'   => [false, true],
            ],
            'method'   => [
                'scope'       => ['public'],
                'abstract'    => [false, true],
                'constructor' => [false, true],
                'destructor'  => [false, true],
                'final'       => [false, true],
                'static'      => [false, true],
                'closure'     => [false, true],
                'deprecated'  => [false, true],
                'generator'   => [false, true],
                'internal'    => [false, true],
                'variadic'    => [false, true],
            ],
            'property' => [
                'scope'   => ['public'],
                'default' => [false, true],
                'static'  => [false, true]
            ]
        ];
        ReStructuredText::filter($filter);

        $dir = dirname(__DIR__) . '/data';
        $dir = 'F:\git\github\FizeDb\src';
        $output = dirname(__DIR__) . '/output2';


        ReStructuredText::dir($dir, $output, 'fize\db');
    }
}
