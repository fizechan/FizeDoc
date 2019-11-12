<?php


namespace fizedoc\test;

/**
 * 这个是测试类
 *
 * 要写一大堆东西的啦，巴拉巴拉
 * 这是详情，可以多行，巴拉巴拉
 * @author FizeChan
 * @notice 这是你要注意的事项
 * @notice 这是第二个注意事项
 */
class TestDoc
{

    /**
     * 测试方法1
     */
    public static function testMethod1()
    {
        echo '123';
    }

    /**
     * 测试方法2
     *
     * 要写一大堆东西的啦，巴拉巴拉
     * 这是详情，可以多行，巴拉巴拉
     * @param string $name 名称
     * @param int $age 年龄
     * @return string 返回一个字符串标识
     */
    public function testMethod2($name, $age = 30)
    {
        return "{$name}-{$age}";
    }
}