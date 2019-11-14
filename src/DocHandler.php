<?php

namespace fize\doc;


use Reflection;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\Param;

/**
 * 解析符合PSR4标准的源码，并生成对应文档格式
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
    protected static $filter = [
        'class'    => [
            'abstract'     => [false, true],
            'anonymous'    => [false, true],
            'cloneable'    => [false, true],
            'final'        => [false, true],
            'instantiable' => [false, true],
            'interface'    => [false, true],
            'internal'     => [false, true],
            'iterable'     => [false, true],
            'iterateable ' => [false, true],
            'trait'        => [false, true],
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

    /**
     * 初始化
     * @param string $class 类全限定名
     */
    public function __construct($class)
    {
        $this->class = $class;
        $this->reflectionClass = new ReflectionClass($class);
        $this->docBlockFactory = DocBlockFactory::createInstance();
    }

    /**
     * 解析类信息
     * @return string
     */
    abstract protected function class();

    /**
     * 解析类大纲
     * @return string
     */
    abstract protected function outline();

    /**
     * 解析类常量
     * @return string
     */
    abstract protected function constants();

    /**
     * 解析类属性
     * @return string
     */
    abstract protected function properties();

    /**
     * 解析类方法
     * @return string
     */
    abstract protected function methods();

    /**
     * 解析
     * @return string
     */
    abstract public function parse();

    /**
     * 解析代码文件夹
     * @param string $dir 文件夹路径
     * @param string $output 导出的文档路径
     * @param string $namespace 命名空间
     */
    abstract public static function dir($dir, $output, $namespace = '');

    /**
     * 注册自动加载，在解析文档前执行
     * @param string $dir 要自动加载的文件夹
     * @param string $namespace 命名空间
     * @noinspection PhpIncludeInspection
     */
    public static function register($dir, $namespace = '')
    {
        spl_autoload_register(function ($class) use ($dir, $namespace) {
            $class = str_replace($namespace, '', $class);
            $file = str_replace('\\', DIRECTORY_SEPARATOR, "{$dir}/{$class}.php");
            if (!is_file($file)) {
                return false;
            }
            require_once $file;
            return true;
        }, false, true);
    }

    /**
     * 设置过滤器
     * @param array $filter 过滤器
     */
    public static function filter(array $filter)
    {
        self::$filter = $filter;
    }

    /**
     * 取得常量数组
     * @return ReflectionClassConstant[]
     */
    protected function getConstants()
    {
        $filter = self::$filter['constant'];
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
    protected function getMethods()
    {
        $filter = self::$filter['method'];
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
            if ($scope_map && $abstract_map && $constructor_map && $destructor_map && $final_map && $static_map && $closure_map && $deprecated_map && $generator_map && $internal_map && $variadic_map) {
                $methods[] = $method;
            }
        }
        return $methods;
    }

    /**
     * 获取属性数组
     * @return ReflectionProperty[]
     */
    protected function getProperties()
    {
        $filter = self::$filter['property'];
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
    protected static function formatShowVariable($variable)
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
     * 获取方法的参数DOC注释
     * @param ReflectionMethod $method
     * @return array
     */
    protected function getMethodParametersDoc(ReflectionMethod $method)
    {
        $doc = $method->getDocComment();
        if(!$doc) {
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
                'name'         => $param->getName(),
                'type'         => (string)$param->getType(),
                'description'  => $param->getDescription()->render(),
                'isVariadic'   => $param->isVariadic(),
            ];
        }
        return $docs;
    }

    /**
     * 获取方法定义
     * @param ReflectionMethod $method 方法
     * @return string
     */
    protected function getMethodDefinition(ReflectionMethod $method)
    {
        $str = '';

        $modifiers = Reflection::getModifierNames($method->getModifiers());
        $modifiers = $modifiers ? implode(' ', $modifiers) : '';
        $str .= $modifiers . ' function ' . $method->getName() . '(';

        $str_parameter_left = '';
        $str_parameter_right = '';
        $docs = $this->getMethodParametersDoc($method);
        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();
            $str_parameter_part = '';

            if($str_parameter_left) {
                $str_parameter_part = ', ';
            }
            if(isset($docs[$name])) {
                $str_parameter_part .= $docs[$name]['type'] .' ';
            } else {
                $str_parameter_part .= 'mixed ';
            }
            if($parameter->isCallable()) {
                $str_parameter_part .= '&';
            }
            $str_parameter_part .= '$' . $name;

            if($parameter->isOptional() && !$parameter->isVariadic()) {
                try {
                    $str_parameter_part .= ' = ' . self::formatShowVariable($parameter->getDefaultValue());
                } catch (\Exception $exception) {
                    var_dump($method);
                    var_dump($parameter);
                }


                $str_parameter_left .= ' [';
                $str_parameter_right .= ']';
            }
            $str_parameter_left .= $str_parameter_part;
        }
        $str .= $str_parameter_left;
        if ($str_parameter_right) {
            $str .= ' ' . $str_parameter_right;
        }

        $str .= ')';

        $doc = $method->getDocComment();
        if($doc) {
            $docblock = $this->docBlockFactory->create($doc);
            $returns = $docblock->getTagsByName('return');
            if ($returns) {
                /**
                 * @var Return_
                 */
                $return = $returns[0];
                $return_type = $return->getType();
                $str .= ' : ' . $return_type;
            }
        }elseif ($method->hasReturnType()) {
            $return_type = 'mixed';
            $str .= ' : ' . $return_type;
        }

        return $str;
    }
}