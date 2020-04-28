<?php

namespace fize\doc\handler;

use Reflection;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use fize\io\Directory;
use fize\io\File;
use fize\doc\DocHandler;
use fize\doc\driver\Markdown as Mk;

/**
 * 生成 md 文档
 *
 * 解析源码，并生成对应 md 文档格式
 */
class Markdown extends DocHandler
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
            $str .= Mk::title($summary, 1);
            $str .= "\r\n";
            //描述
            $description = $docblock->getDescription();
            $description = $description->render();
            if ($description) {
                $str .= Mk::block($description);
            }
        }
        $str .= "\r\n";

        //属性
        $headers = [
            'attr'  => '属性',
            'value' => '值'
        ];
        $datas = [];

        $namespace = Mk::original($this->reflectionClass->getNamespaceName());
        $datas[] = [
            'attr'  => '命名空间',
            'value' => $namespace,
        ];

        $classname = Mk::original($this->reflectionClass->getShortName());
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
            $parent_class = Mk::original($parent_class->getName());
            $datas[] = [
                'attr'  => '父类',
                'value' => $parent_class //@todo 超链接
            ];
        }

        $interfaces = $this->reflectionClass->getInterfaceNames();
        if ($interfaces) {
            $interfaces = implode(', ', $interfaces);
            $interfaces = Mk::original($interfaces);
            $datas[] = [
                'attr'  => '实现接口',
                'value' => $interfaces  //@todo 超链接
            ];
        }

        $str .= Mk::table($datas, $headers, false);
        $str .= "\r\n";

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
            $str .= Mk::field('常量', '');
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
                $value = Mk::original(self::formatShowVariable($value));
                $doc = $constant->getDocComment();
                $summary = '';
                if ($doc) {
                    $docblock = $this->docBlockFactory->create($doc);
                    $summary = Mk::original($docblock->getSummary());
                }
                //$modifiers = Reflection::getModifierNames($constant->getModifiers());
                //$modifiers = $modifiers ? implode(' ', $modifiers) : '';
                $datas[] = [
                    //'modifiers' => $modifiers,
                    'name'    => Mk::link($name, "#{$name}"),
                    'type'    => $type,
                    'value'   => $value,
                    'summary' => $summary,
                ];
            }
            $str .= Mk::table($datas, $headers, false);
            $str .= "\r\n";
        }

        //属性
        $properties = $this->getProperties();
        if ($properties) {
            $str .= Mk::field('属性', "\r\n");
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
                $summary = Mk::original($summary);
                //$type = Rst::original($type);
                //$modifiers = Reflection::getModifierNames($property->getModifiers());
                //$modifiers = $modifiers ? implode(' ', $modifiers) : '';
                $datas[] = [
                    //'modifiers' => $modifiers,
                    'name'    => Mk::link($name, "#" . self::toMdAnchor($name)),
                    //'type'      => $type,
                    'summary' => $summary,
                ];
            }

            $str .= Mk::table($datas, $headers, false);
            $str .= "\r\n";
        }

        //方法
        $methods = $this->getMethods();
        if ($methods) {
            $str .= Mk::field('方法', "\r\n");
            $headers = [
                //'modifiers' => '修饰符',
                'name'    => '方法名',
                //'return'    => '返回类型',
                'summary' => '说明',
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
                         * @var Return_ $return
                         */
                        //$return = $returns[0];
                        //$return = $this->formatType($return->getType());
                    }
                }
                $summary = Mk::original($summary);
                //$return = Rst::original($return);
                //$modifiers = Reflection::getModifierNames($method->getModifiers());
                //$modifiers = $modifiers ? implode(' ', $modifiers) : '';
                $datas[] = [
                    //'modifiers' => $modifiers,
                    'name'    => Mk::link("$name\(\)", "#" . self::toMdAnchor("{$name}()")),
                    //'return'    => $return,
                    'summary' => $summary,
                ];
            }

            $str .= Mk::table($datas, $headers, false);
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
            $str .= Mk::title('常量', 2);
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

                $str .= Mk::title($name, 3);
                if ($summary) {
                    $str .= $summary;
                }
                $str .= "\r\n\r\n";
                $str .= Mk::field('修饰符', $modifiers);
                $str .= Mk::field('类型', $type);
                $str .= Mk::field('值', $value);
                if ($desc) {
                    $str .= Mk::block($desc);
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
            $str .= Mk::title('属性', 2);
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

                $str .= Mk::title($name, 3);
                if ($summary) {
                    $str .= $summary;
                }
                $str .= "\r\n\r\n";
                $str .= Mk::field('修饰符', $modifiers);
                $str .= Mk::field('类型', $type);
                $str .= Mk::field('默认值', $value);
                if ($var_desc) {
                    $str .= Mk::block($var_desc);
                }
                if ($desc) {
                    $str .= Mk::block($desc);
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
            $str .= Mk::title('方法', 2);
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

                $str .= Mk::title($name . "()", 3);
                if ($summary) {
                    $str .= $summary;
                }
                $str .= "\r\n\r\n";

                $str .= Mk::code('php', $this->getMethodDefinition($method));

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
                    $str_table = Mk::table($datas, $headers);
                    $str .= Mk::field('参数', "\r\n" . $str_table, false);
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
                            $str .= Mk::field('返回值', $return_desc);
                        }
                    }
                }

                if ($desc) {
                    $str .= Mk::block($desc);
                }

                //@todo 说明及用例

                $str .= "\r\n";
            }
        }
        return $str;
    }

    /**
     * 解析
     * @return string 返回MD格式文档字符串
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
    public static function file($file, $output, $namespace = '', $filters = null, $check = false)
    {
        $pathinfo = pathinfo($file);
        self::registerAutoload($pathinfo['dirname'], $namespace);
        $mk = new static($namespace . '\\' . $pathinfo['filename'], $filters);
        if ($check && !$mk->checkClassFilters()) {
            return false;
        }
        $content = $mk->parse();
        $fso = new File($output, 'w+');
        $fso->putContents($content);
        return true;
    }

    /**
     * 解析代码文件夹
     * @param string     $dir       文件夹路径
     * @param string     $output    保存文档的根目录
     * @param string     $namespace 命名空间
     * @param string     $in        存放导出文档的目录
     * @param array      $map       文件夹命名规范
     * @param array|bool $filters   过滤器
     */
    public static function dir($dir, $output, $namespace = '', $in = null, array $map = [], $filters = null)
    {
        $idx_content = self::dirParse($dir, $output, $namespace, $in, $map, $filters);
        if ($in) {
            $title = $in;
            if (isset($map[0])) {
                $title = $map[0];
            }
            $idx_content = "* [{$title}]({$in}/README.md)\n" . $idx_content;
            if (File::exists($output . "/{$in}/README.md")) {
                $fso = new File($output . "/{$in}/README.md", 'r');
                $fso->copy($output . "/{$in}", "README.md." . date("YmdHis") . ".bak");
            }
            $fso = new File($output . "/{$in}/README.md", 'w+');
            $fso->putContents("# {$title}\n");
        }
        if (File::exists($output . "/README.md")) {
            $fso = new File($output . "/README.md", 'r');
            $fso->copy($output, "README.md." . date("YmdHis") . ".bak");
        }
        $fso = new File($output . '/README.md', 'w+');
        $fso->putContents("# namespace：{$namespace}");

        if (File::exists($output . "/SUMMARY.md")) {
            $fso = new File($output . "/SUMMARY.md", 'r');
            $fso->copy($output, "SUMMARY.md." . date("YmdHis") . ".bak");
        }
        $fso = new File($output . '/SUMMARY.md', 'w+');
        $idx_content = str_replace(Mk::original("{$namespace}\\"), "", $idx_content);  //缩短命名空间
        $fso->putContents($idx_content);
    }

    /**
     * 解析代码文件夹
     * @param string     $dir       文件夹路径
     * @param string     $output    保存文档的根目录
     * @param string     $namespace 命名空间
     * @param string     $in        存放导出文档的目录
     * @param array      $map       文件夹命名规范
     * @param array|bool $filters   过滤器
     * @return string 返回md用于生成目录的内容
     */
    private static function dirParse($dir, $output, $namespace = '', $in = null, array $map = [], $filters = null)
    {
        self::registerAutoload($dir, $namespace);

        $org_output = $output;
        if ($in) {
            $output .= "/{$in}";
        }

        if (!Directory::isDir($output)) {
            Directory::mk($output, 0777, true);
        }

        $idx_content = '';

        $items = Directory::scan($dir);
        foreach ($items as $item) {
            $path = $dir . '/' . $item;
            if (Directory::isDir($path)) {
                if ($item == '.' || $item == '..') {
                    continue;
                }

                $sub_map = [];
                if (isset($map[1]) && isset($map[1][$item])) {
                    $sub_map = $map[1][$item];
                }

                $idx_content .= self::dirParse($path, $org_output, $namespace . '\\' . $item, $in, $sub_map, $filters);
            } else {
                $pathinfo = pathinfo($path);

                $save_file = self::toMdFilename($namespace . '\\' . $pathinfo['filename']) . '.md';

                $result = self::file($path, $output . '/' . $save_file, $namespace, $filters, true);
                if ($result) {
                    if ($in) {
                        $idx_content .= "   * [" . Mk::original($namespace . '\\' . $pathinfo['filename']) . "]({$in}/{$save_file})\n";
                    } else {
                        $idx_content .= "* [" . Mk::original($namespace . '\\' . $pathinfo['filename']) . "]({$save_file})\n";
                    }
                }
            }
        }

        return $idx_content;
    }

    /**
     * 将类名转化为md文件名
     * @param string $class 类名
     * @return string
     */
    private static function toMdFilename($class)
    {
        $name = self::uncamelize($class);
        $name = str_replace('\\', '_', $name);
        return $name;
    }

    /**
     * 返回md定义的锚点名称
     *
     * md锚点转化需要满足一定格式
     * @param string $str 原字符串
     * @return string
     */
    private static function toMdAnchor($str)
    {
        $str = strtolower($str);
        $str = str_replace(' ', '-', $str);
        $str = str_replace('_', '', $str);
        $str = str_replace('(', '', $str);
        $str = str_replace(')', '', $str);
        //%28%29 为字符串“()”的实体
        return $str;
    }
}
