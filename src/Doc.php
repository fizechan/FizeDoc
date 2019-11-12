<?php
/** @noinspection PhpIncludeInspection */


namespace fize\doc;

use ReflectionClass;
use ReflectionException;
use phpDocumentor\Reflection\DocBlockFactory;

/**
 * 解析符合PSR4标准的源码，并生成对应文档格式
 */
class Doc
{

    public static function file($file, $output, $namespace_pre = '')
    {

    }

    static public function dir($dir, $output, $namespace_pre = '')
    {

    }

    /**
     * @var ReflectionClass 待解析的类
     */
    protected $class;

    /**
     * 初始化
     * @param string $file 类文件路径
     * @param string $namespace_pre 命名空间前缀
     * @throws ReflectionException
     */
    public function __construct($file, $namespace_pre = '')
    {
        require_once $file;
        $class = basename($file, '.php');
        if($namespace_pre) {
            $class = "{$namespace_pre}\\{$class}";
        }
        $this->class = new ReflectionClass($class);
    }
	
	/**
     * 大纲
     * @return array
     */
	public function getOutline()
	{
		
	}

    public function getClassDoc()
    {
        return $this->class->getDocComment();
    }

    public function getTags($doc)
    {
        $factory  = DocBlockFactory::createInstance();
        $docblock = $factory->create($doc);
        return $docblock->getTags();
    }

    public function getTag($doc, $tag)
    {
        $factory  = DocBlockFactory::createInstance();
        $docblock = $factory->create($doc);
        return $docblock->getTagsByName($tag);
    }
}