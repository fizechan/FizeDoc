<?php

namespace handler;

use fize\doc\handler\ReStructuredText;
use PHPUnit\Framework\TestCase;

class ReStructuredTextTest extends TestCase
{

    public function testDir()
    {
        $bool = is_dir("F:\git\github\output\FizeDoc");
        var_dump($bool);
        die();

        $filter = [
            'class'    => [
                'abstract'     => [false, true],
                'anonymous'    => [false, true],
                'cloneable'    => [false, true],
                'final'        => [false, true],
                'instantiable' => [false, true],
                'interface'    => [false, true],
                'internal'     => [false],
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
                'internal'   => [false],
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
                'internal'    => [false],
                'variadic'    => [false, true],
            ],
            'property' => [
                'scope'   => ['public'],
                'default' => [false, true],
                'static'  => [false, true]
            ]
        ];
        ReStructuredText::filter($filter);

//        $dir = 'F:\git\github\Fize\FizeDatetime\src';
//        $output = dirname(__DIR__) . '/output6';
//        ReStructuredText::dir($dir, $output, 'fize\datetime');
//        return;

//        $dir = 'F:\git\github\Fize\FizeCrypt\src';
//        $output = dirname(__DIR__) . '/output5';
//        ReStructuredText::dir($dir, $output, 'fize\crypt');
//        return;
//
//        $dir = 'F:\git\github\Fize\FizeIo\src';
//        $output = dirname(__DIR__) . '/output4';
//        ReStructuredText::dir($dir, $output, 'fize\io');
//        return;
//
//        $dir = 'F:\git\github\Fize\FizeMisc\src';
//        $output = dirname(__DIR__) . '/output3';
//        ReStructuredText::dir($dir, $output, 'fize\misc');
//        return;

        $map = [
            '类库参考',
            [
                'handler' => ['处理器']
            ]
        ];
        $dir = 'F:\git\github\Fize\FizeCache\src';
        $output = dirname(__DIR__) . '/output/cache';


        ReStructuredText::dir($dir, $output, 'fize\cache', $map);
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
