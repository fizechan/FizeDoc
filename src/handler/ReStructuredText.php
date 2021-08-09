<?php

namespace fize\doc\handler;

use fize\doc\driver\ReStructuredText as Rst;
use fize\doc\DocHandler;
use fize\io\Directory;
use fize\io\File;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use Reflection;

/**
 * 解析源码，并生成对应 rst 文档格式
 */
class ReStructuredText extends DocHandler
{

    /**
     * 解析类信息
     * @return string
     */
    protected function class(): string
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
            if ($description) {
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
                'value' => $parent_class //@todo 超链接
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

        return $str;
    }

    /**
     * 解析类总览
     * @return string
     */
    protected function outline(): string
    {
        $str = '';

        //常量
        $constants = $this->getConstants();
        if ($constants) {
            $str .= Rst::field('常量', '', false, 0);
            $headers = [
                //'modifiers' => '修饰符',
                'name'    => '名称',
                'type'    => '类型',
                'value'   => '值',
                'summary' => '说明',
            ];
            $datas = [];
            foreach ($constants as $constant) {
                $name = $constant->getName();
                $value = $constant->getValue();
                $type = $this->formatType(gettype($value));
                $value = Rst::original(self::formatShowVariable($value));
                $doc = $constant->getDocComment();
                $summary = '';
                if ($doc) {
                    $docblock = $this->docBlockFactory->create($doc);
                    $summary = Rst::original($docblock->getSummary());
                }
                //$modifiers = Reflection::getModifierNames($constant->getModifiers());
                //$modifiers = $modifiers ? implode(' ', $modifiers) : '';
                $datas[] = [
                    //'modifiers' => $modifiers,
                    'name'    => Rst::link($name),
                    'type'    => $type,
                    'value'   => $value,
                    'summary' => $summary,
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
                //'modifiers' => '修饰符',
                'name'    => '名称',
                //'type'      => '类型',
                'summary' => '说明',
            ];
            $datas = [];
            foreach ($properties as $property) {
                $name = $property->getName();
                //$type = 'unknown';
                $doc = $property->getDocComment();
                $summary = '';
                if ($doc) {
                    $docblock = $this->docBlockFactory->create($doc);
                    $summary = $docblock->getSummary();
                    $vars = $docblock->getTagsByName('var');
                    if ($vars) {
                        /**
                         * @var Var_ $var
                         */
                        $var = $vars[0];
                        //$type = $this->formatType($var->getType());
                        $desc = $var->getDescription();
                        if ($desc) {
                            $summary = $desc;
                        }
                    }
                }
                $summary = Rst::original($summary);
                //$type = Rst::original($type);
                //$modifiers = Reflection::getModifierNames($property->getModifiers());
                //$modifiers = $modifiers ? implode(' ', $modifiers) : '';
                $datas[] = [
                    //'modifiers' => $modifiers,
                    'name'    => Rst::link($name),
                    //'type'      => $type,
                    'summary' => $summary,
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
                //'modifiers' => '修饰符',
                'name'    => '方法名',
                //'return'    => '返回类型',
                'summary' => '说明',
            ];
            $datas = [];
            foreach ($methods as $method) {
                $name = $method->getName();
//                $return = 'void';
                $doc = $method->getDocComment();
                $summary = '';
                if ($doc) {
                    $docblock = $this->docBlockFactory->create($doc);
                    $summary = $docblock->getSummary();
//                    $returns = $docblock->getTagsByName('return');
//                    if ($returns) {
//                        /**
//                         * @var Return_ $return
//                         */
//                        $return = $returns[0];
//                        $return = $this->formatType($return->getType());
//                    }
                }
                $summary = Rst::original($summary);
                //$return = Rst::original($return);
                //$modifiers = Reflection::getModifierNames($method->getModifiers());
                //$modifiers = $modifiers ? implode(' ', $modifiers) : '';
                $datas[] = [
                    //'modifiers' => $modifiers,
                    'name'    => Rst::link($name . '()'),
                    //'return'    => $return,
                    'summary' => $summary,
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
    protected function constants(): string
    {
        $str = '';
        $constants = $this->getConstants();
        if ($constants) {
            $str .= Rst::title('常量', 2);
            foreach ($constants as $constant) {
                $name = $constant->getName();
                $value = $constant->getValue();
                $type = $this->formatType(gettype($value));
                $value = self::formatShowVariable($value);
                $doc = $constant->getDocComment();
                $summary = '';
                $desc = '';
                if ($doc) {
                    $docblock = $this->docBlockFactory->create($doc);
                    $summary = $docblock->getSummary();
                    $desc = $docblock->getDescription();
                    if ($desc) {
                        $desc = $desc->render();
                    }
                }
                $modifiers = Reflection::getModifierNames($constant->getModifiers());
                $modifiers = $modifiers ? implode(' ', $modifiers) : '';

                $str .= Rst::title($name, 3);
                if ($summary) {
                    $str .= $summary;
                }
                $str .= "\r\n\r\n";
                $str .= Rst::field('修饰符', $modifiers);
                $str .= Rst::field('类型', $type);
                $str .= Rst::field('值', $value);
                if ($desc) {
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
    protected function properties(): string
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
                    if ($desc) {
                        $desc = $desc->render();
                    }
                    $vars = $docblock->getTagsByName('var');
                    if ($vars) {
                        /**
                         * @var Var_ $var
                         */
                        $var = $vars[0];
                        $type = $this->formatType($var->getType());
                        $var_desc = $var->getDescription();
                    }
                }
                $modifiers = Reflection::getModifierNames($property->getModifiers());
                $modifiers = $modifiers ? implode(' ', $modifiers) : '';
                $value = key_exists($name, $default_properties) ? self::formatShowVariable($default_properties[$name]) : 'null';

                $str .= Rst::title($name, 3);
                if ($summary) {
                    $str .= $summary;
                }
                $str .= "\r\n\r\n";
                $str .= Rst::field('修饰符', $modifiers);
                $str .= Rst::field('类型', $type);
                $str .= Rst::field('默认值', $value);
                if ($var_desc) {
                    $str .= Rst::block($var_desc);
                }
                if ($desc) {
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
    protected function methods(): string
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
                    if ($desc) {
                        $desc = $desc->render();
                    }
                }

                $str .= Rst::title($name . '()', 3);
                if ($summary) {
                    $str .= $summary;
                }
                $str .= "\r\n\r\n";

                $str .= Rst::directive('code-block', 'php', [], $this->getMethodDefinition($method));

                $parameters = $method->getParameters();
                if ($parameters) {

                    $headers = [
                        'name'    => '名称',
                        'summary' => '说明',
                    ];
                    $datas = [];
                    $docs = $this->getMethodParametersDoc($method);
                    foreach ($parameters as $parameter) {
                        $name = $parameter->getName();
                        $datas[] = [
                            'name'    => $name,
                            'summary' => isset($docs[$name]) ? $docs[$name]['description'] : '',
                        ];
                    }
                    $str_table = Rst::table($datas, $headers);
                    $str .= Rst::field('参数', $str_table, false);
                }

                if ($doc) {
                    $docblock = $this->docBlockFactory->create($doc);
                    $returns = $docblock->getTagsByName('return');
                    if ($returns) {
                        /**
                         * @var Return_ $return
                         */
                        $return = $returns[0];
                        $return_desc = $return->getDescription();
                        if ($return_desc && (string)$return_desc) {
                            $str .= Rst::field('返回值', $return_desc);
                        }
                    }
                }

                if ($desc) {
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
    public function parse(): string
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
    public static function file(string $file, string $output, string $namespace = '', $filters = null, bool $check = false): bool
    {
        $pathinfo = pathinfo($file);
        self::registerAutoload($pathinfo['dirname'], $namespace);
        $rst = new static($namespace . '\\' . $pathinfo['filename'], $filters);
        if ($check && !$rst->checkClassFilters()) {
            return false;
        }
        $content = $rst->parse();
        $fso = new File($output, 'w+');
        $fso->putContents($content);
        return true;
    }

    /**
     * 解析代码文件夹
     * @param string      $dir       文件夹路径
     * @param string      $output    保存文档的根目录
     * @param string      $namespace 命名空间
     * @param string|null $in        存放导出文档的目录
     * @param array       $map       文件夹命名规范
     * @param array|bool  $filters   过滤器
     */
    public static function dir(string $dir, string $output, string $namespace = '', string $in = null, array $map = [], $filters = null)
    {
        if ($in) {
            $output = $output . '/' . $in;
        }

        self::registerAutoload($dir, $namespace);

        if (!Directory::exists($output)) {
            new Directory($output, true);
        }

        $idxcontent = '';

        $items = (new Directory($dir))->scan();
        foreach ($items as $item) {
            $path = $dir . '/' . $item;
            if (Directory::exists($path)) {
                if ($item == '.' || $item == '..') {
                    continue;
                }

                if ($idxcontent) {
                    $idxcontent .= "\n";
                }
                $idxcontent .= $item . "/index";

                $sub_map = [];
                if (isset($map[1]) && isset($map[1][$item])) {
                    $sub_map = $map[1][$item];
                }

                self::dir($path, $output . '/' . $item, $namespace . '\\' . $item, null, $sub_map, $filters);
            } else {
                $pathinfo = pathinfo($path);
                $save_file = $output . '/' . self::uncamelize($pathinfo['filename']) . '.rst';
                $result = self::file($path, $save_file, $namespace, $filters, true);
                if ($result) {
                    if ($idxcontent) {
                        $idxcontent .= "\n";
                    }
                    $idxcontent .= self::uncamelize($pathinfo['filename']);
                }
            }
        }
        //创建index.rst
        $idxstr = '';
        $title = basename($dir);
        if (isset($map[0])) {
            $title = $map[0];
        }
        $idxstr .= Rst::title($title, 1);
        $idxstr .= "\r\n\r\n";
        $idxstr .= Rst::directive('toctree', '', ['maxdepth' => 2, 'glob' => null], $idxcontent);
        $fso = new File($output . '/index.rst', 'w+');
        $fso->putContents($idxstr);
    }
}
