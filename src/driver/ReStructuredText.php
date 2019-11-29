<?php


namespace fize\doc\driver;

/**
 * ReStructuredText 驱动类
 *
 * 用于生成 rst 格式
 */
class ReStructuredText
{
    /**
     * 原样输出字符串
     * @param string $str 待输出字符串
     * @param array $replaces 待替换字符
     * @return string
     */
    public static function original($str, array $replaces = [])
    {
        if (!$replaces) {
            $replaces = ['\\'];
        }
        foreach ($replaces as $replace) {
            $str = str_replace($replace, '\\' . $replace, $str);
        }
        return $str;
    }

    /**
     * 标题
     * @param string $title 标题
     * @param int $level 级别
     * @param bool $original 是否原样输出标题
     * @return string
     */
    public static function title($title, $level, $original = true)
    {
        if($original) {
            $title = self::original($title);
        }
        $str_len = strlen($title);
        $modifiers = ['=', '=', '-', '^', '"', '*'];
        $modifier = $modifiers[$level - 1];
        $str = '';
        $str .= $title. "\r\n";
        $str .= str_repeat($modifier, $str_len) . "\r\n";
        if($level == 1) {
            $str = str_repeat($modifier, $str_len) . "\r\n" . $str;
        }
        return $str;
    }

    /**
     * 修饰字符串
     * @param string $str 待修饰字符串
     * @param string $modifier 修饰符
     * @return string
     */
    public static function modify($str, $modifier)
    {
        return $modifier . $str . $modifier;
    }

    /**
     * 修饰：强调
     * @param string $str 待修饰字符串
     * @return string
     */
    public static function modifyEmphasis($str)
    {
        return self::modify($str, '*');
    }

    /**
     * 修饰：引用
     * @param string $str 待修饰字符串
     * @return string
     */
    public static function modifyQuote($str)
    {
        return self::modify($str, '`');
    }

    /**
     * 文字块
     * @param string $content 内容
     * @param int $indent 缩进
     * @param bool $original 是否原样输出
     * @return string
     */
    public static function block($content, $indent = 4, $original = true)
    {
        $str = '';
        $str .= "\r\n";
        $str .= "::\r\n\r\n";
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            if($original) {
                $line = self::original($line);
            }
            $str .= str_repeat(' ', $indent) . $line . "\r\n";
        }
        $str .= "\r\n";
        return $str;
    }

    /**
     * 字符串长度，中文算2个，ascii字符算1个
     * @param string $str 字符串
     * @return int
     */
    protected static function abslength($str)
    {
        //规定占位为1的字符
        $fix1s = ['“', '”'];
        foreach ($fix1s as $fix1) {
            $str = str_replace($fix1, '*', $str);
        }
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
     * 简单表格
     * @param array $rows 数据
     * @param array $headers 表头
     * @param bool $original 是否原样输出字符串(即非转义)
     * @return string
     */
    protected static function simpleTable(array $rows, array $headers = [], $original = true)
    {
        if($original) {
            $temp_headers = [];
            foreach ($headers as $key => $title) {
                $title = self::original($title);
                $temp_headers[$key] = $title;
            }
            $headers = $temp_headers;
            $temp_rows = [];
            foreach ($rows as $row) {
                $tem_row = $row;
                if($headers) {
                    foreach ($headers as $key => $title) {
                        $tem_row[$key] = self::original($tem_row[$key]);
                    }
                } else {
                    foreach ($row as $idx => $value) {
                        $tem_row[$idx] = self::original($value);
                    }
                }
                $temp_rows[] = $tem_row;
            }
            $rows = $temp_rows;
        }

        $lens = [];
        $index = 0;
        if($headers) {
            foreach ($headers as $key => $title) {
                $len = strlen($title);
                foreach ($rows as $row) {
                    $t_len = strlen($row[$key]);
                    if ($t_len > $len) {
                        $len = $t_len;
                    }
                }
                $lens[] = $len + 1;
                $index++;
            }
        } else {
            for ($idx = 1; $idx < count($rows[0]); $idx ++) {
                $len = 0;
                foreach ($rows as $row) {
                    $t_len = strlen($row[$idx]);
                    if ($t_len > $len) {
                        $len = $t_len;
                    }
                }
                $lens[] = $len + 1;
                $index++;
            }
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

        if($headers) {
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
        }

        foreach ($rows as $row) {
            $index = 0;
            if($headers) {
                foreach ($headers as $key => $title) {
                    $str .= self::cnStrPad($row[$key], $lens[$index], ' ');
                    if ($index < $len_count - 1) {
                        $str .= ' ';
                    } else {
                        $str .= "\r\n";
                    }
                    $index++;
                }
            } else {
                foreach ($row as $idx => $value) {
                    $str .= self::cnStrPad($value, $lens[$index], ' ');
                    if ($index < $len_count - 1) {
                        $str .= ' ';
                    } else {
                        $str .= "\r\n";
                    }
                    $index++;
                }
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
        $str .= "\r\n";
        return $str;
    }

    /**
     * 网格表格
     * @param array $headers 表头
     * @param array $rows 数据
     * @param bool $original 是否原样输出字符串(即非转义)
     * @return string
     */
    protected static function gridTable(array $rows, array $headers = [], $original = true)
    {
        if($original) {
            $temp_headers = [];
            foreach ($headers as $key => $title) {
                $title = self::original($title);
                $temp_headers[$key] = $title;
            }
            $headers = $temp_headers;
            $temp_rows = [];
            foreach ($rows as $row) {
                $tem_row = $row;
                if($headers) {
                    foreach ($headers as $key => $title) {
                        $tem_row[$key] = self::original($tem_row[$key]);
                    }
                } else {
                    foreach ($row as $idx => $value) {
                        $tem_row[$idx] = self::original($value);
                    }
                }
                $temp_rows[] = $tem_row;
            }
            $rows = $temp_rows;
        }

        $lens = [];
        $index = 0;
        if($headers) {
            foreach ($headers as $key => $title) {
                $len = strlen($title);
                foreach ($rows as $row) {
                    $t_len = strlen($row[$key]);
                    if ($t_len > $len) {
                        $len = $t_len;
                    }
                }
                $lens[] = $len + 1;
                $index++;
            }
        } else {
            for ($idx = 1; $idx < count($rows[0]); $idx ++) {
                $len = 0;
                foreach ($rows as $row) {
                    $t_len = strlen($row[$idx]);
                    if ($t_len > $len) {
                        $len = $t_len;
                    }
                }
                $lens[] = $len + 1;
                $index++;
            }
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

        if($headers) {
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
        }

        foreach ($rows as $row) {
            $index = 0;
            $str .= '|';
            if($headers) {
                foreach ($headers as $key => $title) {
                    $str .= self::cnStrPad($row[$key], $lens[$index], ' ');
                    if ($index < $len_count - 1) {
                        $str .= '|';
                    } else {
                        $str .= "|\r\n";
                    }
                    $index++;
                }
            } else {
                foreach ($row as $idx => $value) {
                    $str .= self::cnStrPad($value, $lens[$index], ' ');
                    if ($index < $len_count - 1) {
                        $str .= '|';
                    } else {
                        $str .= "|\r\n";
                    }
                    $index++;
                }
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
        $str .= "\r\n";
        return $str;
    }

    /**
     * 表格
     * @param array $headers 表头
     * @param array $rows 数据
     * @param bool $original 是否原样输出字符串(即非转义)
     * @param bool $simple 是否使用简易表格
     * @return string
     */
    public static function table(array $rows, array $headers = [], $original = true, $simple = false)
    {
        if($simple) {
            $str = self::simpleTable($rows, $headers, $original);
        } else {
            $str = self::gridTable($rows, $headers, $original);
        }
        return $str;
    }

    /**
     * 链接
     * @param string $title 显示字眼
     * @param string $url 链接URL
     * @return string
     */
    public static function link($title = '', $url = '')
    {
        $str = '';
        if($title) {
            $str .= $title;
        }
        if($url) {
            $str .= " <{$url}>";
        }
        $str = '`' . $str . '`_';
        return $str;
    }

    /**
     * 字段
     * @param string $name 字段名
     * @param string $desc 详细描述
     * @param bool $original 是否原样输出详细描述
     * @param int $indent 缩进
     * @return string
     */
    public static function field($name, $desc, $original = true, $indent = 2)
    {
        $desc = str_replace("\r\n", "\n", $desc);

        $str = '';
        $str .= ":{$name}:\r\n";

        $lines = explode("\n", $desc);
        foreach ($lines as $line) {
            if($original) {
                $line = self::original($line);
            }
            $str .= str_repeat(' ', $indent) . $line . "\r\n";
        }
        $str .= "\r\n";
        return $str;
    }

    /**
     * 指令
     * @param string $name 指令名称
     * @param string $desc 指令明细
     * @param array $options 指令选项
     * @param string $content 附加内容
     * @return string
     */
    public static function directive($name, $desc, array $options = [], $content = '')
    {
        $desc = str_replace("\r\n", "\n", $desc);

        $str = '';
        $str .= ".. {$name}::";
        if($desc) {
            $str .= " {$desc}";
        }
        $str .= "\r\n";
        if($options) {
            foreach ($options as $key => $value) {
                $str .= "  :{$key}:";
                if(!is_null($value)) {
                    $str .= " {$value}";
                }
                $str .= "\r\n";
            }
        }
        $str .= "\r\n";
        if($content) {
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $str .= '  ' . $line . "\r\n";
            }
            $str .= "\r\n";
        }
        $str .= "\r\n";
        return $str;
    }
}