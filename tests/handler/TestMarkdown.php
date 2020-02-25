<?php

namespace handler;

use fize\doc\handler\Markdown;
use PHPUnit\Framework\TestCase;

class TestMarkdown extends TestCase
{

    public function testParse()
    {
        $class = "fize\\doc\\handler\\Markdown";
        $mk = new Markdown($class);
        $str = $mk->parse();
        self::assertIsString($str);
        echo $str;
    }

    public function testFile()
    {
        $file = 'F:\git\github\Fize\FizeDoc\src\handler\Markdown.php';
        $output = dirname(__DIR__) . '/../temp/Markdown.md';
        $namespace = 'fize\doc\handler';
        Markdown::file($file, $output, $namespace);
        self::assertTrue(true);
    }

    public function testDir()
    {
        $map = [
            '类库参考',
            [
                'handler' => ['处理器']
            ]
        ];
        $dir = 'F:\git\github\Fize\FizeDoc\src';
        $output = dirname(__DIR__) . '/../temp/mk';

        Markdown::dir($dir, $output, 'fize\doc', 'libraries', $map);
        self::assertTrue(true);
    }
}
