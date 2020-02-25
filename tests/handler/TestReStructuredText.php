<?php

namespace handler;

use fize\doc\handler\ReStructuredText;
use PHPUnit\Framework\TestCase;

class TestReStructuredText extends TestCase
{
    public function testParse()
    {
        $class = "fize\\doc\\handler\\ReStructuredText";
        $mk = new ReStructuredText($class);
        $str = $mk->parse();
        self::assertIsString($str);
        echo $str;
    }

    public function testFile()
    {
        $file = 'F:\git\github\Fize\FizeDoc\src\handler\ReStructuredText.php';
        $output = dirname(__DIR__) . '/../temp/ReStructuredText.rst';
        $namespace = 'fize\doc\handler';
        ReStructuredText::file($file, $output, $namespace);
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
        $output = dirname(__DIR__) . '/../temp/rst';

        ReStructuredText::dir($dir, $output, 'fize\doc', 'libraries', $map);
        self::assertTrue(true);
    }
}
