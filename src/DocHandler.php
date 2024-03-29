<?php

namespace Fize\Doc;

use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use Reflection;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;

/**
 * 解析源码，并生成对应文档格式
 */
abstract class DocHandler
{

    /**
     * @var string 类全限定名
     */
    protected $class;

    /**
     * @var ReflectionClass 待解析的类
     */
    protected $reflectionClass;

    /**
     * @var DocBlockFactory 文档解析器
     */
    protected $docBlockFactory;

    /**
     * @var array 过滤器
     */
    protected $filters;

    /**
     * 初始化
     *
     * 参数 `$filters`:
     *   array类型：在默认情况下再追加指定配置
     *   bool类型：true：默认情况；false：返回全部
     * @param string     $class   类全限定名
     * @param array|bool $filters 过滤器
     */
    public function __construct(string $class, $filters = null)
    {
        $this->class = $class;
        $this->reflectionClass = new ReflectionClass($class);
        $this->docBlockFactory = DocBlockFactory::createInstance();

        $this->filters = $this->getFiltersDefault();
        if (is_array($filters)) {
            $this->filter($filters);
        } elseif ($filters === false) {
            $this->filter($this->getFiltersAll());
        } else {
            $this->filter($this->getFiltersDefault());
        }
    }

    /**
     * 解析类信息
     * @return string
     */
    abstract protected function class(): string;

    /**
     * 解析类大纲
     * @return string
     */
    abstract protected function outline(): string;

    /**
     * 解析类常量
     * @return string
     */
    abstract protected function constants(): string;

    /**
     * 解析类属性
     * @return string
     */
    abstract protected function properties(): string;

    /**
     * 解析类方法
     * @return string
     */
    abstract protected function methods(): string;

    /**
     * 解析
     * @return string
     */
    abstract public function parse(): string;

    /**
     * 解析代码文件
     *
     * 如果定义了类过滤器且$check为true，在条件不符合的情况不生成文档
     * @param string     $file      文件路径
     * @param string     $output    导出的文档路径
     * @param string     $namespace 命名空间
     * @param array|bool $filters   过滤器
     * @param bool       $check     是否检测类过滤器
     * @return bool 是否生成文档
     */
    abstract public static function file(string $file, string $output, string $namespace = '', $filters = null, bool $check = false): bool;

    /**
     * 解析代码文件夹
     * @param string      $dir       文件夹路径
     * @param string      $output    保存文档的根目录
     * @param string      $namespace 命名空间
     * @param string|null $in        存放导出文档的目录
     * @param array       $map       文件夹命名规范
     * @param array|bool  $filters   过滤器
     */
    abstract public static function dir(string $dir, string $output, string $namespace = '', string $in = null, array $map = [], $filters = null);

    /**
     * 注册自动加载
     * @param string $dir       要自动加载的文件夹
     * @param string $namespace 命名空间
     */
    protected static function registerAutoload(string $dir, string $namespace = '')
    {
        static $registerd_namespaces = [];
        if (!isset($registerd_namespaces[$namespace])) {
            spl_autoload_register(function ($class) use ($dir, $namespace) {
                $class = str_replace($namespace, '', $class);
                $file = str_replace('\\', DIRECTORY_SEPARATOR, "$dir/$class.php");
                if (!is_file($file)) {
                    return false;
                }
                require_once $file;
                return true;
            }, false, true);
            $registerd_namespaces[$namespace] = true;
        }
    }

    /**
     * 设置过滤器
     * @param array $filters 过滤器
     */
    protected function filter(array $filters)
    {
        foreach ($filters as $type => $filter) {
            $filter = array_merge($this->filters[$type], $filter);
            $this->filters[$type] = $filter;
        }
    }

    /**
     * 取得常量数组
     * @return ReflectionClassConstant[]
     */
    protected function getConstants(): array
    {
        $filter = $this->filters['constant'];
        $constants = [];
        foreach ($this->reflectionClass->getReflectionConstants() as $constant) {
            $scope_map1 = in_array('public', $filter['scope']) && $constant->isPublic();
            $scope_map2 = in_array('protected', $filter['scope']) && $constant->isProtected();
            $scope_map3 = in_array('private', $filter['scope']) && $constant->isPrivate();
            if ($scope_map1 || $scope_map2 || $scope_map3) {
                $constants[] = $constant;
            }
        }
        return $constants;
    }

    /**
     * 取得方法数组
     * @return ReflectionMethod[]
     */
    protected function getMethods(): array
    {
        $filter = $this->filters['method'];
        $methods = [];
        foreach ($this->reflectionClass->getMethods() as $method) {
            $scope_map1 = in_array('public', $filter['scope']) && $method->isPublic();
            $scope_map2 = in_array('protected', $filter['scope']) && $method->isProtected();
            $scope_map3 = in_array('private', $filter['scope']) && $method->isPrivate();
            $scope_map = $scope_map1 || $scope_map2 || $scope_map3;
            $abstract_map = in_array($method->isAbstract(), $filter['abstract']);
            $constructor_map = in_array($method->isConstructor(), $filter['constructor']);
            $destructor_map = in_array($method->isDestructor(), $filter['destructor']);
            $final_map = in_array($method->isFinal(), $filter['final']);
            $static_map = in_array($method->isStatic(), $filter['static']);
            $closure_map = in_array($method->isClosure(), $filter['closure']);
            $deprecated_map = in_array($method->isDeprecated(), $filter['deprecated']);
            $generator_map = in_array($method->isGenerator(), $filter['generator']);
            $internal_map = in_array($method->isInternal(), $filter['internal']);
            $variadic_map = in_array($method->isVariadic(), $filter['variadic']);
            if (
                $scope_map && $abstract_map && $constructor_map && $destructor_map && $final_map &&
                $static_map && $closure_map && $deprecated_map && $generator_map && $internal_map && $variadic_map
            ) {
                $methods[] = $method;
            }
        }
        return $methods;
    }

    /**
     * 获取属性数组
     * @return ReflectionProperty[]
     */
    protected function getProperties(): array
    {
        $filter = $this->filters['property'];
        $properties = [];
        foreach ($this->reflectionClass->getProperties() as $property) {
            $scope_map1 = in_array('public', $filter['scope']) && $property->isPublic();
            $scope_map2 = in_array('protected', $filter['scope']) && $property->isProtected();
            $scope_map3 = in_array('private', $filter['scope']) && $property->isPrivate();
            $scope_map = $scope_map1 || $scope_map2 || $scope_map3;
            $default_map = in_array($property->isDefault(), $filter['default']);
            $static_map = in_array($property->isStatic(), $filter['static']);
            if ($scope_map && $default_map && $static_map) {
                $properties[] = $property;
            }
        }
        return $properties;
    }

    /**
     * 变量格式化显示
     * @param mixed $variable 变量
     * @return string
     */
    protected static function formatShowVariable($variable): string
    {
        if (is_array($variable)) {
            return json_encode($variable, JSON_UNESCAPED_UNICODE);
        }
        if (is_object($variable)) {
            return 'object{}';
        }
        if (is_bool($variable)) {
            return $variable ? 'true' : 'false';
        }
        if (is_null($variable)) {
            return 'null';
        }
        if (is_string($variable)) {
            return '"' . addslashes($variable) . '"';
        }
        return (string)print_r($variable, true);
    }

    /**
     * 统一参数类型显示
     * @param string $type 参数类型
     * @return string
     */
    protected function formatType(string $type): string
    {
        $types = explode('|', $type);
        $tstrs = [];
        $map = [
            'integer' => 'int',
            'boolean' => 'bool'
        ];
        foreach ($types as $type) {
            if (array_key_exists($type, $map)) {
                $type = $map[$type];
            }
            if (strpos($type, '\\') === 0) {
                $code = file_get_contents($this->reflectionClass->getFileName());
                $type_name = substr($type, 1);
                if (preg_match("/[\n]+[\s]*use[\s]+([^\s]+)[\s]*as[\s]+" . preg_quote($type_name) . "[\s]*;[\s]*[\n]*/", $code, $matches)) {
                    //形如“use fize\db\definition\Db as Base;”的情况
                    $type = '\\' . $matches[1];
                } elseif (preg_match("/[\n]+[\s]*use[\s]+([^\s]+\\" . preg_quote($type) . ")[\s]*;[\s]*[\n]*/", $code, $matches)) {
                    //形如“use fize\db\definition\Db;”的情况
                    $type = '\\' . $matches[1];
                } elseif (class_exists('\\' . $this->reflectionClass->getNamespaceName() . $type)) {
                    //类在同一个命名空间的情况
                    $type = '\\' . $this->reflectionClass->getNamespaceName() . $type;
                } elseif (interface_exists('\\' . $this->reflectionClass->getNamespaceName() . $type)) {
                    //接口在同一个命名空间的情况
                    $type = '\\' . $this->reflectionClass->getNamespaceName() . $type;
                }
            }
            $tstrs[] = $type;
        }

        return implode('|', $tstrs);
    }

    /**
     * 获取方法的参数DOC注释
     * @param ReflectionMethod $method 方法
     * @return array
     */
    protected function getMethodParametersDoc(ReflectionMethod $method): array
    {
        $doc = $method->getDocComment();
        if (!$doc) {
            return [];
        }
        $docblock = $this->docBlockFactory->create($doc);
        $params = $docblock->getTagsByName('param');
        $docs = [];
        foreach ($params as $param) {
            /**
             * @var Param $param
             */
            $docs[$param->getVariableName()] = [
                'name'        => $param->getName(),
                'type'        => $this->formatType($param->getType()),
                'description' => $param->getDescription()->render(),
                'isVariadic'  => $param->isVariadic(),
            ];
        }
        return $docs;
    }

    /**
     * 获取方法定义
     * @param ReflectionMethod $method 方法
     * @return string
     */
    protected function getMethodDefinition(ReflectionMethod $method): string
    {
        $str = '';

        $modifiers = Reflection::getModifierNames($method->getModifiers());
        $modifiers = $modifiers ? implode(' ', $modifiers) : '';
        $str .= $modifiers . ' function ' . $method->getName() . ' (';

        $str_parameter = '';
        $docs = $this->getMethodParametersDoc($method);
        foreach ($method->getParameters() as $parameter) {
            if ($str_parameter) {
                $str_parameter .= ",\n";
            }

            $name = $parameter->getName();
            $str_parameter .= '    ';
            if (isset($docs[$name])) {
                $str_parameter .= $docs[$name]['type'] . ' ';
            } else {
                $str_parameter .= 'mixed ';
            }
            if ($parameter->isPassedByReference()) {
                $str_parameter .= '&';
            }
            if ($parameter->isVariadic()) {
                $str_parameter .= '...';
            }
            $str_parameter .= '$' . $name;

            if ($parameter->isOptional() && !$parameter->isVariadic()) {
                $str_parameter .= ' = ' . self::formatShowVariable($parameter->getDefaultValue());
            }
        }

        if ($str_parameter) {
            $str .= "\n";
            $str .= $str_parameter;
            $str .= "\n";
        }

        $str .= ')';

        $doc = $method->getDocComment();
        if ($doc) {
            $docblock = $this->docBlockFactory->create($doc);
            $returns = $docblock->getTagsByName('return');
            if ($returns) {
                /**
                 * @var Return_ $return
                 */
                $return = $returns[0];
                $return_type = $this->formatType($return->getType());
                $str .= ' : ' . $return_type;
            }
        } elseif ($method->hasReturnType()) {
            $return_type = 'mixed';
            $str .= ' : ' . $return_type;
        }

        return $str;
    }

    /**
     * 驼峰命名转间隔符命名
     * @param string $camelCaps 待转化字符串
     * @param string $separator 间隔符
     * @return string
     */
    protected static function uncamelize(string $camelCaps, string $separator = '_'): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

    /**
     * 全部过滤器
     * @return array
     */
    private function getFiltersAll(): array
    {
        return [
            'class'    => [
                'abstract'     => [false, true],  // 抽象类
                'anonymous'    => [false, true],  // 匿名类
                'cloneable'    => [false, true],  // 可复制
                'final'        => [false, true],  // final
                'instantiable' => [false, true],  // 可实例化
                'interface'    => [false, true],  // 接口
                'internal'     => [false, true],  // 系统类
                'iterateable'  => [false, true],  // 可迭代
                'trait'        => [false, true],  // trait
            ],
            'constant' => [
                'scope' => ['public', 'protected', 'private']
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
                'scope'       => ['public', 'protected', 'private'],
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
                'scope'   => ['public', 'protected', 'private'],
                'default' => [false, true],
                'static'  => [false, true]
            ]
        ];
    }

    /**
     * 默认过滤器
     * @return array
     */
    private function getFiltersDefault(): array
    {
        return [
            'class'    => [
                'abstract'     => [false],
                'anonymous'    => [false],
                'cloneable'    => [false, true],
                'final'        => [false, true],
                'instantiable' => [false, true],
                'interface'    => [false],
                'internal'     => [false],
                'iterateable'  => [false, true],
                'trait'        => [false],
            ],
            'constant' => [
                'scope' => ['public']
            ],
            'function' => [
                'disabled'   => [false],
                'closure'    => [false],
                'deprecated' => [false, true],
                'generator'  => [false, true],
                'internal'   => [false],
                'variadic'   => [false, true],
            ],
            'method'   => [
                'scope'       => ['public'],
                'abstract'    => [false],
                'constructor' => [false, true],
                'destructor'  => [false, true],
                'final'       => [false, true],
                'static'      => [false, true],
                'closure'     => [false],
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
    }

    /**
     * 检测当前类是否通过过滤器
     * @return bool
     */
    public function checkClassFilters(): bool
    {
        $filter = $this->filters['class'];

        $abstract_map = in_array($this->reflectionClass->isAbstract(), $filter['abstract']);
        $anonymous_map = in_array($this->reflectionClass->isAnonymous(), $filter['anonymous']);
        $cloneable_map = in_array($this->reflectionClass->isCloneable(), $filter['cloneable']);
        $final_map = in_array($this->reflectionClass->isFinal(), $filter['final']);
        $instantiable_map = in_array($this->reflectionClass->isInstantiable(), $filter['instantiable']);
        $interface_map = in_array($this->reflectionClass->isInterface(), $filter['interface']);
        $internal_map = in_array($this->reflectionClass->isInternal(), $filter['internal']);
        $iterateable_map = in_array($this->reflectionClass->isIterateable(), $filter['iterateable']);
        $trait_map = in_array($this->reflectionClass->isTrait(), $filter['trait']);

        return $abstract_map && $anonymous_map && $cloneable_map && $final_map && $instantiable_map &&
            $interface_map && $internal_map && $iterateable_map && $trait_map;
    }
}
