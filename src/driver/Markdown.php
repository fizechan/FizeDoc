<?php

namespace fize\doc\driver;

/**
 * Markdown 驱动
 *
 * 用于生成 md 格式
 */
class Markdown
{
    /**
     * 原样输出字符串
     * @param string $str      待输出字符串
     * @param array  $replaces 待替换字符
     * @return string
     */
    public static function original(string $str, array $replaces = []): string
    {
        if (!$replaces) {
            $replaces = ['\\', '=', '-', '#', '*', '_', '~', '[', ']', '^', '(', ')'];
        }
        foreach ($replaces as $replace) {
            $str = str_replace($replace, '\\' . $replace, $str);
        }
        return $str;
    }

    /**
     * 标题
     * @param string $title    标题
     * @param int    $level    级别
     * @param bool   $original 是否原样输出标题
     * @return string
     */
    public static function title(string $title, int $level, bool $original = true): string
    {
        if ($original) {
            $title = self::original($title);
        }
        return str_repeat('#', $level) . " " . $title . "\r\n";
    }

    /**
     * 修饰字符串
     * @param string $str      待修饰字符串
     * @param string $modifier 修饰符
     * @return string
     */
    public static function modify(string $str, string $modifier): string
    {
        return $modifier . $str . $modifier;
    }

    /**
     * 修饰：强调
     * @param string $str 待修饰字符串
     * @return string
     */
    public static function modifyEmphasis(string $str): string
    {
        return self::modify($str, '*');
    }

    /**
     * 修饰：引用
     * @param string $content 待修饰字符串
     * @return string
     */
    public static function modifyQuote(string $content): string
    {
        $str = '';
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $str .= "> " . $line . "\r\n";
        }
        $str .= "\r\n";
        return $str;

        //return "> {$str}";
    }

    /**
     * 文字块
     * @param string $content  内容
     * @param bool   $original 是否原样输出
     * @return string
     */
    public static function block(string $content, bool $original = true): string
    {
        $str = '';
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            if ($original) {
                $line = self::original($line);
            }
            $str .= str_repeat(' ', 4) . $line . "\r\n";
        }
        $str .= "\r\n";
        return $str;
    }

    /**
     * 表格
     * @param array $headers  表头
     * @param array $rows     数据
     * @param bool  $original 是否原样输出字符串(即非转义)
     * @return string
     */
    public static function table(array $rows, array $headers = [], bool $original = true): string
    {
        if ($original) {
            $temp_headers = [];
            foreach ($headers as $key => $title) {
                $title = self::original($title);
                $temp_headers[$key] = $title;
            }
            $headers = $temp_headers;
            $temp_rows = [];
            foreach ($rows as $row) {
                $tem_row = $row;
                if ($headers) {
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
        if ($headers) {
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
            for ($idx = 1; $idx < count($rows[0]); $idx++) {
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

        if ($headers) {
            $index = 0;
            foreach ($headers as $title) {
                if ($index == 0) {
                    $str .= '|';
                }
                $str .= self::cnStrPad($title, $lens[$index]);
                if ($index < $len_count - 1) {
                    $str .= '|';
                } else {
                    $str .= "|\r\n";
                }
                $index++;
            }
            foreach ($lens as $index => $len) {
                if ($index == 0) {
                    $str .= '|';
                }
                $str .= self::cnStrPad('', $len, '-');
                if ($index < $len_count - 1) {
                    $str .= '|';
                } else {
                    $str .= "|\r\n";
                }
            }
        }

        foreach ($rows as $row) {
            $index = 0;
            if ($headers) {
                foreach ($headers as $key => $title) {
                    if ($index == 0) {
                        $str .= '|';
                    }
                    $str .= self::cnStrPad($row[$key], $lens[$index]);
                    if ($index < $len_count - 1) {
                        $str .= '|';
                    } else {
                        $str .= "|\r\n";
                    }
                    $index++;
                }
            } else {
                foreach ($row as $value) {
                    if ($index == 0) {
                        $str .= '|';
                    }
                    $str .= self::cnStrPad($value, $lens[$index]);
                    if ($index < $len_count - 1) {
                        $str .= '|';
                    } else {
                        $str .= "|\r\n";
                    }
                    $index++;
                }
            }
        }
        $str .= "\r\n";
        return $str;
    }

    /**
     * 链接
     * @param string      $content 显示内容
     * @param string      $url     链接URL
     * @param string|null $title   标题
     * @return string
     */
    public static function link(string $content, string $url, string $title = null): string
    {
        if ($title) {
            return "[$content]($url \"$title\")";
        } else {
            return "[$content]($url)";
        }
    }

    /**
     * 字段
     * @param string $name     字段名
     * @param string $desc     详细描述
     * @param bool   $original 是否原样输出详细描述
     * @return string
     */
    public static function field(string $name, string $desc, bool $original = true): string
    {
        if ($original) {
            $desc = self::original($desc);
        }
        return "**$name**\r\n> $desc";
    }

    /**
     * 代码块
     * @param string $lang    语言
     * @param string $content 内容
     * @return string
     */
    public static function code(string $lang, string $content): string
    {
        $str = "```$lang\r\n";
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $str .= $line . "\r\n";
        }
        $str .= "```\r\n";
        return $str;
    }

    /**
     * 字符串长度，中文算2个，ascii字符算1个
     * @param string $str 字符串
     * @return int
     */
    protected static function abslength(string $str): int
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
     * @param string $input      字符串
     * @param int    $pad_length 长度
     * @param string $pad_string 填充字符
     * @param int    $pad_type   填充类型
     * @return string
     */
    protected static function cnStrPad(string $input, int $pad_length, string $pad_string = " ", int $pad_type = 1): string
    {
        $pad_plus = strlen($input) - self::abslength($input);
        $pad_length = $pad_length + $pad_plus;
        return str_pad($input, $pad_length, $pad_string, $pad_type);
    }
}
