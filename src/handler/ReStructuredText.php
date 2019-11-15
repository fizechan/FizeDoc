<?php


namespace fize\doc\handler;

use fize\doc\DocHandler;
use fize\doc\driver\ReStructuredText as Rst;
use Reflection;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;

/**
 * 解析符合PSR4标准的源码，并生成对应rst文档格式
 *
 * 这些东西很麻烦啊！~！
 * 你看看怎么办吧！！！
 */
class ReStructuredText extends DocHandler
{

    /**
     * 解析类信息
     * @return string
     */
    protected function class()
    {
        $str = '';

        $doc = $this->reflectionClass->getDocComment();
        if ($doc) {
            $docblock = $this->docBlockFactory->create($doc);
            $summary = $docblock->getSummary();
            //标题
            $str .= Rst::title($summary, 1);
            $str .= "\r\n";
            //描述
            $description = $docblock->getDescription();
            $description = $description->render();
            if($description) {
                $str .= Rst::block($description);
            }
        }
        $str .= "\r\n";

        //属性
        $headers = [
            'attr'  => '属性',
            'value' => '值'
        ];
        $datas = [];

        $namespace = Rst::original($this->reflectionClass->getNamespaceName());
        $datas[] = [
            'attr'  => '命名空间',
            'value' => $namespace,
        ];

        $classname = Rst::original($this->reflectionClass->getShortName());
        $datas[] = [
            'attr'  => '类名',
            'value' => $classname
        ];

        $modifiers = Reflection::getModifierNames($this->reflectionClass->getModifiers());
        $modifiers = $modifiers ? implode(' ', $modifiers) : '';
        if ($modifiers) {
            $datas[] = [
                'attr'  => '修饰符',
                'value' => $modifiers
            ];
        }

        $parent_class = $this->reflectionClass->getParentClass();
        if ($parent_class) {
            $parent_class = Rst::original($parent_class->getName());
            $datas[] = [
                'attr'  => '父类',
                'value' =>  $parent_class //@todo 超链接
            ];
        }

        $interfaces = $this->reflectionClass->getInterfaceNames();
        if ($interfaces) {
            $interfaces = implode(', ', $interfaces);
            $interfaces = Rst::original($interfaces);
            $datas[] = [
                'attr'  => '实现接口',
                'value' => $interfaces  //@todo 超链接
            ];
        }

        $str .= Rst::table($datas, $headers, false);
        $str .= "\r\n";

        //$str .= Rst::directive('contents', '', ['local' => null]);

        return $str;
    }

    /**
     * 解析类总览
     * @return string
     */
    protected function outline()
    {
        $str = '';

        //常量
        $constants = $this->getConstants();
        if ($constants) {
            $str .= Rst::field('常量', '', false, 0);
            $headers = [
                'modifiers' => '修饰符',
                'name'      => '名称',
                'type'      => '类型',
                'value'     => '值',
                'summary'   => '说明',
            ];
            $datas = [];
            foreach ($constants as $constant) {
                $name = $constant->getName();
                $value = $constant->getValue();
                $type = gettype($value);
                $value = Rst::original(self::formatShowVariable($value));
                $doc = $constant->getDocComment();
                $summary = '';
                if ($doc) {
                    $docblock = $this->docBlockFactory->create($doc);
                    $summary = Rst::original($docblock->getSummary());
                }
                $modifiers = Reflection::getModifierNames($constant->getModifiers());
                $modifiers = $modifiers ? implode(' ', $modifiers) : '';
                $datas[] = [
                    'modifiers' => $modifiers,
                    'name'      => Rst::link($name),
                    'type'      => $type,
                    'value'     => $value,
                    'summary'   => $summary,
                ];
            }
            $str .= Rst::table($datas, $headers, false);
            $str .= "\r\n";
        }

        //属性
        $properties = $this->getProperties();
        if ($properties) {
            $str .= Rst::field('属性', '', false, 0);
            $headers = [
                'modifiers' => '修饰符',
                'name'      => '名称',
                'type'      => '类型',
                'summary'   => '说明',
            ];
            $datas = [];
            foreach ($properties as $property) {
                $name = $property->getName();
                $type = 'unknown';
                $doc = $property->getDocComment();
                $summary = '';
                if ($doc) {
                    $docblock = $this->docBlockFactory->create($doc);
                    $summary = $docblock->getSummary();
                    $vars = $docblock->getTagsByName('var');
                    if ($vars) {
                        /**
                         * @var Var_
                         */
                        $var = $vars[0];
                        $type = $var->getType();
                        $desc = $var->getDescription();
                        if ($desc) {
                            $summary = $desc;
                        }
                    }
                }
                $summary = Rst::original($summary);
                $type = Rst::original($type);
                $modifiers = Reflection::getModifierNames($property->getModifiers());
                $modifiers = $modifiers ? implode(' ', $modifiers) : '';
                $datas[] = [
                    'modifiers' => $modifiers,
                    'name'      => Rst::link($name),
                    'type'      => $type,
                    'summary'   => $summary,
                ];
            }

            $str .= Rst::table($datas, $headers, false);
            $str .= "\r\n";
        }

        //方法
        $methods = $this->getMethods();
        if ($methods) {
            $str .= Rst::field('方法', '', false, 0);
            $headers = [
                'modifiers' => '修饰符',
                'name'      => '名称',
                'return'    => '返回类型',
                'summary'   => '说明',
            ];
            $datas = [];
            foreach ($methods as $method) {
                $name = $method->getName();
                $return = 'void';
                $doc = $method->getDocComment();
                $summary = '';
                if ($doc) {
                    $docblock = $this->docBlockFactory->create($doc);
                    $summary = $docblock->getSummary();
                    $returns = $docblock->getTagsByName('return');
                    if ($returns) {
                        /**
                         * @var Return_
                         */
                        $return = $returns[0];
                        $return = $return->getType();
                    }
                }
                $summary = Rst::original($summary);
                $return = Rst::original($return);
                $modifiers = Reflection::getModifierNames($method->getModifiers());
                $modifiers = $modifiers ? implode(' ', $modifiers) : '';
                $datas[] = [
                    'modifiers' => $modifiers,
                    'name'      => Rst::link($name . '()'),
                    'return'    => $return,
                    'summary'   => $summary,
                ];
            }

            $str .= Rst::table($datas, $headers, false);
            $str .= "\r\n";
        }
        return $str;
    }

    /**
     * 解析类常量
     * @return string
     */
    protected function constants()
    {
        $str = '';
        $constants = $this->getConstants();
        if ($constants) {
            $str .= Rst::title('常量', 2);
            foreach ($constants as $constant) {
                $name = $constant->getName();
                $value = $constant->getValue();
                $type = gettype($value);
                $value = self::formatShowVariable($value);
                $doc = $constant->getDocComment();
                $summary = '';
                $desc = '';
                if ($doc) {
                    $docblock = $this->docBlockFactory->create($doc);
                    $summary = $docblock->getSummary();
                    $desc = $docblock->getDescription();
                    if($desc) {
                        $desc = $desc->render();
                    }
                }
                $modifiers = Reflection::getModifierNames($constant->getModifiers());
                $modifiers = $modifiers ? implode(' ', $modifiers) : '';

                $str .= Rst::title($name, 3);
                if($summary) {
                    $str .= Rst::modifyEmphasis($summary);
                }
                $str .= "\r\n\r\n";
                $str .= Rst::field('修饰符', $modifiers);
                $str .= Rst::field('类型', $type);
                $str .= Rst::field('值', $value);
                if($desc) {
                    $str .= Rst::block($desc);
                }

                //@todo 说明及用例

                $str .= "\r\n";
            }
        }
        return $str;
    }

    /**
     * 解析类属性
     * @return string
     */
    protected function properties()
    {
        $str = '';
        $properties = $this->getProperties();
        if ($properties) {
            $str .= Rst::title('属性', 2);
            $default_properties = $this->reflectionClass->getDefaultProperties();
            foreach ($properties as $property) {
                $name = $property->getName();
                $type = 'unknown';
                $doc = $property->getDocComment();
                $var_desc = '';
                $summary = '';
                $desc = '';
                if ($doc) {
                    $docblock = $this->docBlockFactory->create($doc);
                    $summary = $docblock->getSummary();
                    $desc = $docblock->getDescription();
                    if($desc) {
                        $desc = $desc->render();
                    }
                    $vars = $docblock->getTagsByName('var');
                    if ($vars) {
                        /**
                         * @var Var_
                         */
                        $var = $vars[0];
                        $type = $var->getType();  //@todo 对于对象类型的处理
                        $var_desc = $var->getDescription();
                    }
                }
                $modifiers = Reflection::getModifierNames($property->getModifiers());
                $modifiers = $modifiers ? implode(' ', $modifiers) : '';
                $value = key_exists($name, $default_properties) ? self::formatShowVariable($default_properties[$name]) : 'null';

                $str .= Rst::title($name, 3);
                if($summary) {
                    $str .= Rst::modifyEmphasis($summary);
                }
                $str .= "\r\n\r\n";
                $str .= Rst::field('修饰符', $modifiers);
                $str .= Rst::field('类型', $type);
                $str .= Rst::field('默认值', $value);
                if($desc) {
                    $str .= Rst::block($desc);
                }

                //@todo 说明及用例

                $str .= "\r\n";
            }
        }
        return $str;
    }

    /**
     * 解析类方法
     * @return string
     */
    protected function methods()
    {
        $str = '';
        $methods = $this->getMethods();
        if ($methods) {
            $str .= Rst::title('方法', 2);
            foreach ($methods as $method) {
                $name = $method->getName();
                $doc = $method->getDocComment();
                $summary = '';
                $desc = '';
                if ($doc) {
                    $docblock = $this->docBlockFactory->create($doc);
                    $summary = $docblock->getSummary();
                    $desc = $docblock->getDescription();
                    if($desc) {
                        $desc = $desc->render();
                    }
                }

                $str .= Rst::title($name . '()', 3);
                if($summary) {
                    $str .= Rst::modifyEmphasis($summary);
                }
                $str .= "\r\n\r\n";

                $str .= Rst::directive('code-block', 'php', [], $this->getMethodDefinition($method));

                $parameters = $method->getParameters();
                if ($parameters) {
                    $str .= Rst::field('参数', '', false, 0);
                    $headers = [
                        'name'      => '名称',
                        'summary'   => '说明',
                    ];
                    $datas = [];
                    $docs = $this->getMethodParametersDoc($method);
                    foreach ($parameters as $parameter) {
                        $name = $parameter->getName();
                        $datas[] = [
                            'name'      => $name,
                            'summary'   => isset($docs[$name]) ? $docs[$name]['description'] : '',
                        ];
                    }
                    $str .= Rst::table($datas, $headers);
                }

                if($doc) {
                    $docblock = $this->docBlockFactory->create($doc);
                    $returns = $docblock->getTagsByName('return');
                    if ($returns) {
                        /**
                         * @var Return_
                         */
                        $return = $returns[0];
                        $return_desc = $return->getDescription();
                        if($return_desc && (string)$return_desc) {
                            $str .= Rst::field('返回值', $return_desc);
                        }
                    }
                }

                if($desc) {
                    $str .= Rst::block($desc);
                }

                //@todo 说明及用例

                $str .= "\r\n";
            }
        }
        return $str;
    }

    /**
     * 解析
     * @return string 返回RST格式文档字符串
     */
    public function parse()
    {
        $str = '';
        $str .= $this->class();
        $str .= $this->outline();
        $str .= $this->constants();
        $str .= $this->properties();
        $str .= $this->methods();
        return $str;
    }

    /**
     * 扫描文件夹，生成
     * @param string $dir
     * @param string $output
     * @param string $namespace
     * @param array $map 文件夹命名规范
     */
    public static function dir($dir, $output, $namespace = '', array $map = [])
    {
        static $registerd_namespace = false;
        if(!$registerd_namespace) {
            self::register($dir, $namespace);
            $registerd_namespace = true;
        }

        if(!is_dir($output)) {
            mkdir($output, 0777, true);
        }

        $idxcontent = '';

        $items = scandir($dir);
        foreach($items as $item){
            $path = $dir . '/' . $item;
            if(is_dir($path)){
                if($item == '.' || $item == '..'){
                    continue;
                }

                if($idxcontent){
                    $idxcontent .= "\n";
                }
                $idxcontent .= $item . "/index";

                $submap = [];
                if(isset($map[1]) && isset($map[1][$item])) {
                    $submap = $map[1][$item];
                }

                self::dir($path, $output . '/' . $item, $namespace . '\\' . $item, $submap);
            }else{
                $pathinfo = pathinfo($path);

                if($idxcontent){
                    $idxcontent .= "\n";
                }
                $idxcontent .= self::uncamelize($pathinfo['filename']);

                $save_file = $output . '/' . self::uncamelize($pathinfo['filename']) . '.rst';
                $rst = new self($namespace . '\\' . $pathinfo['filename']);
                $content = $rst->parse();
                file_put_contents($save_file, $content);
            }
        }
        //创建index.rst
        $idxstr = '';
        $title = basename($dir);
        if(isset($map[0])) {
            $title = $map[0];
        }
        $idxstr .= Rst::title($title, 1);
        $idxstr .= "\r\n\r\n";
        $idxstr .= Rst::directive('toctree', '', ['maxdepth' => 2, 'glob' => null], $idxcontent);
        file_put_contents($output . '/index.rst', $idxstr);
    }

    /**
     * 驼峰命名转间隔符命名
     * @param string $camelCaps 驼峰命名待转化字符串
     * @param string $separator 间隔符
     * @return string
     */
    protected static function uncamelize($camelCaps, $separator='_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

    /**
     * 测试方法
     * @param $kkk1
     */
    public static function test_str($kkk1)
    {

    }
}