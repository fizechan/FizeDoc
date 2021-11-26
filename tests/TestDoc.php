<?php

namespace Tests;

use Fize\Doc\Doc;
use PHPUnit\Framework\TestCase;

class TestDoc extends TestCase
{

    public function testFile()
    {
        new Doc('Markdown');
        $file = 'F:\git\github\Fize\FizeDoc\src\handler\Markdown.php';
        $output = dirname(__DIR__) . '/temp/Markdown.md';
        $namespace = 'fize\doc\handler';
        Doc::file($file, $output, $namespace);
        self::assertTrue(true);
    }

    public function testDir()
    {
        new Doc('Markdown');
        $map = [
            '类库参考',
            [
                'handler' => ['处理器']
            ]
        ];
        $dir = 'F:\git\github\Fize\FizeDoc\src';
        $output = dirname(__DIR__) . '/temp/mk';

        Doc::dir($dir, $output, 'fize\doc', 'libraries', $map);
        self::assertTrue(true);
    }
}
