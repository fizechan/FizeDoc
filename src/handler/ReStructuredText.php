<?php
/** @noinspection PhpIncludeInspection */


namespace fize\doc\handler;

use fize\doc\DocHandler;
use Reflection;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;

/**
 * 解析符合PSR4标准的源码，并生成对应rst文档格式
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
            $description = $docblock->getDescription();
            //标题
            $str .= str_repeat('#', strlen($summary)) . "\r\n";
            $str .= $summary . "\r\n";
            $str .= str_repeat('#', strlen($summary)) . "\r\n";
            $str .= "\r\n";
            //描述
            $str .= $description->render() . "\r\n";
        }
        $str .= "\r\n";

        //属性
        $headers = [
            'attr'  => '属性',
            'value' => '值'
        ];
        $datas = [];
        $datas[] = [
            'attr'  => '命名空间',
            'value' => $this->reflectionClass->getNamespaceName(),
        ];
        $datas[] = [
            'attr'  => '类名',
            'value' => $this->reflectionClass->getShortName()
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
            $datas[] = [
                'attr'  => '父类',
                'value' => $parent_class->getName()  //@todo 超链接
            ];
        }
        $interfaces = $this->reflectionClass->getInterfaceNames();
        if ($interfaces) {
            $datas[] = [
                'attr'  => '实现接口',
                'value' => implode(', ', $interfaces)  //@todo 超链接
            ];
        }
        $str .= self::createTable($headers, $datas);
        $str .= "\r\n";

        $str .= ".. contents::\r\n";
        $str .= "  :local:\r\n\r\n";

        return $str;
    }

    /**
     * 解析类总览
     * @return string
     */
    protected function outline()
    {
        $str = '';
        $str .= str_repeat('*', strlen('总览')) . "\r\n";
        $str .= "总览\r\n";
        $str .= str_repeat('*', strlen('总览')) . "\r\n";
        $str .= "\r\n";
        //常量
        $constants = $this->getConstants();
        if ($constants) {
            $str .= "常量\r\n";
            $str .= str_repeat('=', strlen('常量')) . "\r\n";
            $headers = [
                'modifiers' => '修饰符',
                'name'      => '名称',  //@todo 文档内链接
                'type'      => '类型',
                'value'     => '值',
                'summary'   => '说明',
            ];
            $datas = [];
            foreach ($constants as $constant) {
                $name = $constant->getName();
                $value = $constant->getValue();
                $type = gettype($value);
                $value = self::formatShowVariable($value);
                $doc = $constant->getDocComment();
                $summary = '';
                if ($doc) {
                    $docblock = $this->docBlockFactory->create($doc);
                    $summary = $docblock->getSummary();
                }
                $modifiers = Reflection::getModifierNames($constant->getModifiers());
                $modifiers = $modifiers ? implode(' ', $modifiers) : '';
                $datas[] = [
                    'modifiers' => $modifiers,
                    'name'      => $name,
                    'type'      => $type,
                    'value'     => $value,
                    'summary'   => $summary,
                ];
            }

            $str .= self::createTable($headers, $datas);
            $str .= "\r\n";
        }
        //属性
        $properties = $this->getProperties();
        if ($properties) {
            $str .= "属性\r\n";
            $str .= str_repeat('=', strlen('属性')) . "\r\n";
            $headers = [
                'modifiers' => '修饰符',
                'name'      => '名称',  //@todo 文档内链接
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
                $modifiers = Reflection::getModifierNames($property->getModifiers());
                $modifiers = $modifiers ? implode(' ', $modifiers) : '';
                $datas[] = [
                    'modifiers' => $modifiers,
                    'name'      => $name,
                    'type'      => $type,
                    'summary'   => $summary,
                ];
            }

            $str .= self::createTable($headers, $datas);
            $str .= "\r\n";
        }
        //方法
        $methods = $this->getMethods();
        if ($methods) {
            $str .= "方法\r\n";
            $str .= str_repeat('=', strlen('方法')) . "\r\n";
            $headers = [
                'modifiers' => '修饰符',
                'name'      => '名称',  //@todo 文档内链接
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
                $modifiers = Reflection::getModifierNames($method->getModifiers());
                $modifiers = $modifiers ? implode(' ', $modifiers) : '';
                $datas[] = [
                    'modifiers' => $modifiers,
                    'name'      => $name,
                    'return'    => $return,
                    'summary'   => $summary,
                ];
            }

            $str .= self::createTable($headers, $datas);
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
            $str .= str_repeat('*', strlen('常量')) . "\r\n";
            $str .= "常量\r\n";
            $str .= str_repeat('*', strlen('常量')) . "\r\n";
            $str .= "\r\n";
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
                $str .= "{$name}\r\n";
                $str .= str_repeat('=', strlen($name)) . "\r\n";
                $str .= "**{$summary}**\r\n\r\n";
                $str .= "修饰符：*{$modifiers}*；类型：*{$type}*；值：*{$value}*。\r\n";
                if($desc) {
                    $descs = explode("\n", $desc);
                    $str .= "::\r\n\r\n";
                    foreach ($descs as $desc) {
                        $str .= "  {$desc}\r\n";
                    }
                }
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
            $default_properties = $this->reflectionClass->getDefaultProperties();
            $str .= str_repeat('*', strlen('属性')) . "\r\n";
            $str .= "属性\r\n";
            $str .= str_repeat('*', strlen('属性')) . "\r\n";
            $str .= "\r\n";
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
                        $type = $var->getType();
                        $var_desc = $var->getDescription();
                    }
                }
                $modifiers = Reflection::getModifierNames($property->getModifiers());
                $modifiers = $modifiers ? implode(' ', $modifiers) : '';
                $value = key_exists($name, $default_properties) ? self::formatShowVariable($default_properties[$name]) : 'null';
                $str .= "{$name}\r\n";
                $str .= str_repeat('=', strlen($name)) . "\r\n";
                $str .= "**{$var_desc}**\r\n\r\n";
                $str .= "修饰符：*{$modifiers}*；类型：*{$type}*；默认值：*{$value}*。\r\n";
                if($summary) {
                    $str .= "\r\n`{$summary}`\r\n";
                }
                if($desc) {
                    $descs = explode("\n", $desc);
                    $str .= "::\r\n\r\n";
                    foreach ($descs as $desc) {
                        $str .= "  {$desc}\r\n";
                    }
                }
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
            $str .= str_repeat('*', strlen('方法')) . "\r\n";
            $str .= "方法\r\n";
            $str .= str_repeat('*', strlen('方法')) . "\r\n";
            $str .= "\r\n";
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

                $str .= "**{$name}()**\r\n";  //@todo 设置成文档内链接
                $str .= str_repeat('=', strlen($name) + 6) . "\r\n";
                if($summary) {
                    $str .= "**{$summary}**\r\n\r\n";
                }

                $str .= ".. code:: php\r\n\r\n";
                $str .= '  ' . $this->getMethodDefinition($method);
                $str .= "\r\n\r\n";

                $parameters = $method->getParameters();
                if ($parameters) {
                    $str .= "*参数*\r\n\r\n";
                    //$str .= "`参数`\r\n";
                    //$str .= str_repeat('`', strlen('`参数`')) . "\r\n";
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

                    $str .= self::createTable($headers, $datas);
                    $str .= "\r\n";
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
                        if((string)$return_desc) {
                            $str .= "*返回值*\r\n\r\n";
                            //$str .= "`返回值`\r\n";
                            //$str .= str_repeat('`', strlen('`返回值`')) . "\r\n";
                            $str .= $return_desc;
                            $str .= "\r\n";
                        }
                    }
                }

                if($desc) {
                    $descs = explode("\n", $desc);
                    $str .= "::\r\n\r\n";
                    foreach ($descs as $desc) {
                        $str .= "  {$desc}\r\n";
                    }
                }
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
     * @inheritDoc
     */
    public static function dir($dir, $output, $namespace_pre = '')
    {
        self::register($dir, $namespace_pre);
    }

    /**
     * 字符串长度，中文算2个，ascii字符算1个
     * @param string $str 字符串
     * @return int
     */
    protected static function abslength($str)
    {
        return strlen(preg_replace("#[^\x{00}-\x{ff}]#u", '**', $str));
    }

    /**
     * 填充字符串到新长度，中文算2个，ascii字符算1个
     * @param string $input 字符串
     * @param int $pad_length 长度
     * @param string $pad_string 填充字符
     * @param int $pad_type 填充类型
     * @return string
     */
    protected static function cnStrPad($input, $pad_length, $pad_string = " ", $pad_type = 1)
    {
        $pad_plus = strlen($input) - self::abslength($input);
        $pad_length = $pad_length + $pad_plus;
        return str_pad($input, $pad_length, $pad_string, $pad_type);
    }

    /**
     * 创建简单表格
     * @param array $headers 表头
     * @param array $datas 数据
     * @return string
     */
    protected static function createSimpleTable(array $headers, array $datas)
    {
        $lens = [];
        $index = 0;
        foreach ($headers as $key => $title) {
            $len = strlen($title);
            foreach ($datas as $data) {
                $t_len = strlen($data[$key]);
                if ($t_len > $len) {
                    $len = $t_len;
                }
            }
            $lens[] = $len + 1;
            $index++;
        }
        $len_count = count($lens);
        $str = '';
        foreach ($lens as $index => $len) {
            $str .= str_repeat('=', $len);
            if ($index < $len_count - 1) {
                $str .= ' ';
            } else {
                $str .= "\r\n";
            }
        }
        $index = 0;
        foreach ($headers as $key => $title) {
            $str .= self::cnStrPad($title, $lens[$index], ' ');
            if ($index < $len_count - 1) {
                $str .= ' ';
            } else {
                $str .= "\r\n";
            }
            $index++;
        }
        foreach ($lens as $index => $len) {
            $str .= str_repeat('=', $len);
            if ($index < $len_count - 1) {
                $str .= ' ';
            } else {
                $str .= "\r\n";
            }
        }
        foreach ($datas as $data) {
            $index = 0;
            foreach ($headers as $key => $title) {
                $str .= self::cnStrPad($data[$key], $lens[$index], ' ');
                if ($index < $len_count - 1) {
                    $str .= ' ';
                } else {
                    $str .= "\r\n";
                }
                $index++;
            }
        }
        foreach ($lens as $index => $len) {
            $str .= str_repeat('=', $len);
            if ($index < $len_count - 1) {
                $str .= ' ';
            } else {
                $str .= "\r\n";
            }
        }
        return $str;
    }

    /**
     * 创建表格
     * @param array $headers 表头
     * @param array $datas 数据
     * @return string
     */
    protected static function createTable(array $headers, array $datas)
    {
        $lens = [];
        $index = 0;
        foreach ($headers as $key => $title) {
            $len = strlen($title);
            foreach ($datas as $data) {
                $t_len = strlen($data[$key]);
                if ($t_len > $len) {
                    $len = $t_len;
                }
            }
            $lens[] = $len + 1;
            $index++;
        }
        $len_count = count($lens);
        $str = '';
        $str .= '+';
        foreach ($lens as $index => $len) {
            $str .= str_repeat('-', $len);
            if ($index < $len_count - 1) {
                $str .= '+';
            } else {
                $str .= "+\r\n";
            }
        }
        $index = 0;
        $str .= '|';
        foreach ($headers as $key => $title) {
            $str .= self::cnStrPad($title, $lens[$index], ' ');
            if ($index < $len_count - 1) {
                $str .= '|';
            } else {
                $str .= "|\r\n";
            }
            $index++;
        }
        $str .= '+';
        foreach ($lens as $index => $len) {
            $str .= str_repeat('=', $len);
            if ($index < $len_count - 1) {
                $str .= '+';
            } else {
                $str .= "+\r\n";
            }
        }
        foreach ($datas as $data) {
            $index = 0;
            $str .= '|';
            foreach ($headers as $key => $title) {
                $str .= self::cnStrPad($data[$key], $lens[$index], ' ');
                if ($index < $len_count - 1) {
                    $str .= '|';
                } else {
                    $str .= "|\r\n";
                }
                $index++;
            }
            $str .= '+';
            foreach ($lens as $index => $len) {
                $str .= str_repeat('-', $len);
                if ($index < $len_count - 1) {
                    $str .= '+';
                } else {
                    $str .= "+\r\n";
                }
            }
        }
        return $str;
    }
}