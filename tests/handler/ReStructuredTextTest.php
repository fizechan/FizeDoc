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
        $str = $doc->parse();
        echo $str;
    }

    public function testDir()
    {
        ReStructuredText::dir(dirname(__DIR__) . '/data', 'fizedoc\test');
        $doc = new ReStructuredText('fizedoc\test\TestDoc');
        $str = $doc->parse();
        echo $str;
    }
}
