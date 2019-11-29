<?php


namespace fize\doc;

use Exception;

/**
 * 解析 PHP 源码，并生成对应文档格式
 */
class Doc
{

    /**
     * @var DocHandler
     */
    private static $handler;

    /**
     * 常规调用请先初始化
     * @param string $handler 使用的实际接口名称
     */
    public function __construct($handler)
    {
        switch (strtolower($handler)) {
            case 'md':
            case 'markdown':
                $class = '\\' . __NAMESPACE__ . '\\handler\\Markdown';
                break;
            case 'rst':
            case 'restructuredtext':
                $class = '\\' . __NAMESPACE__ . '\\handler\\ReStructuredText';
                break;
            default:
                throw new Exception('fizedoc handler error');
        }
        self::$handler = $class;
    }

    /**
     * 解析代码文件
     * @param string $file 文件路径
     * @param string $output 导出的文档路径
     * @param string $namespace 命名空间
     * @param array $filters 过滤器
     */
    public static function file($file, $output, $namespace = '', array $filters = [])
    {
        self::$handler::file($file, $output, $namespace, $filters);
    }

    /**
     * 解析代码文件夹
     * @param string $dir 代码目录
     * @param string $output 指定生成文档目录
     * @param string $namespace 指定代码的顶级命名空间
     * @param array $map 文件夹在文档中的命名
     * @param array $filters 过滤器
     */
    public static function dir($dir, $output, $namespace = '', array $map = [], array $filters = [])
    {
        self::$handler::dir($dir, $output, $namespace, $map, $filters);
    }
}