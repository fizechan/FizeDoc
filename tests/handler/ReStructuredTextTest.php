<?php

namespace handler;

use fize\doc\handler\ReStructuredText;
use PHPUnit\Framework\TestCase;

class ReStructuredTextTest extends TestCase
{

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

        $dir = 'F:\git\github\Fize\FizeMisc\src';
        $output = dirname(__DIR__) . '/output3';
        ReStructuredText::dir($dir, $output, 'fize\misc');
        return;

        //文件夹定义
        $map = [
            'FizeDb',
            [
                'definition' => ['定义'],
                'exception'  => ['错误'],
                'middleware' => ['中间层',
                    [
                        'driver' => ['驱动']
                    ]
                ],
                'realization' => ['各数据库实现',
                    [
                        'access' => ['Access',
                            ['mode' => ['支持模式']]
                        ],
                        'mssql' => ['MsSQL',
                            ['mode' => ['支持模式']]
                        ],
                        'mysql' => ['MySQL',
                            ['mode' => ['支持模式']]
                        ],
                        'oracle' => ['Oracle',
                            ['mode' => ['支持模式']]
                        ],
                        'pgsql' => ['PostgreSQL',
                            ['mode' => ['支持模式']]
                        ],
                        'sqlite' => ['SQLite3',
                            ['mode' => ['支持模式']]
                        ]
                    ]
                ]
            ]
        ];

        //排序自定义

        //$dir = dirname(__DIR__) . '/data';
        $dir = 'F:\git\github\Fize\FizeDb\src';
        $output = dirname(__DIR__) . '/output2';


        ReStructuredText::dir($dir, $output, 'fize\db', $map);
    }
}
