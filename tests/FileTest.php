<?php

use PHPUnit\Framework\TestCase;
use fize\doc\Doc;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;

class FileTest extends TestCase
{

    public function test__construct()
    {
        $ff = new Doc(TestDoc::class);
        var_dump($ff);
        self::assertIsObject($ff);
    }

    public function testGetClassDoc()
    {
        $ff = new Doc(TestDoc::class);
        $doc = $ff->getClassDoc();
        var_dump($doc);
        self::assertIsString($doc);
    }

    public function testGetTags()
    {
        //$ff = new Doc(TestDoc::class);
        $ff = new Doc(__DIR__ . '/data/TestDoc.php', 'fizedoc\test');
        $doc = $ff->getClassDoc();
        $tags = $ff->getTags($doc);
        //var_dump($tags);
        self::assertIsArray($tags);

        foreach ($tags as $tag) {
            if($tag instanceof Param) {
                var_dump($tag->getVariableName());
                var_dump($tag->getType());

                $description = $tag->getDescription();
                var_dump($description->render());
            } elseif ($tag instanceof Generic) {
                var_dump($tag->getName());

                $description = $tag->getDescription();
                var_dump($description->render());
            }
        }
    }
}
