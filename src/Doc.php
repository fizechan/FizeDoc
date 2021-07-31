<?php

namespace fize\doc;

use RuntimeException;

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
    public function __construct(string $handler)
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
                throw new RuntimeException('fizedoc handler error');
        }
        self::$handler = $class;
    }

    /**
     * 解析代码文件
     * @param string     $file      文件路径
     * @param string     $output    导出的文档路径
     * @param string     $namespace 命名空间
     * @param array|bool $filters   过滤器
     * @param bool       $check     是否检测类过滤器
     * @return bool 是否生成文档
     */
    public static function file(string $file, string $output, string $namespace = '', $filters = null, bool $check = false): bool
    {
        return self::$handler::file($file, $output, $namespace, $filters, $check);
    }

    /**
     * 解析代码文件夹
     * @param string      $dir       代码目录
     * @param string      $output    指定生成文档目录
     * @param string      $namespace 指定代码的顶级命名空间
     * @param string|null $in        存放导出文档的目录
     * @param array       $map       文件夹在文档中的命名
     * @param array|bool  $filters   过滤器
     */
    public static function dir(string $dir, string $output, string $namespace = '', string $in = null, array $map = [], $filters = null)
    {
        self::$handler::dir($dir, $output, $namespace, $in, $map, $filters);
    }
}
