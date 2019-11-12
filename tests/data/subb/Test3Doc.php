<?php


namespace fizedoc\test\subb;

use fizedoc\test\Test2Doc;
use fizedoc\test\NaTaSaInterface;
use fizedoc\test\WorkInterface;

/**
 * 测试类
 *
 * 这是一个测试类，这是我写的描述性文字
 */
 class Test3Doc extends Test2Doc implements NaTaSaInterface, WorkInterface
{

     /**
      * 测试常量1
      *
      * 这就是一个详细介绍。
      * 可以在这里写很多很多的东西。
      */
    const FIZE0 = 0;

     /**
      * 测试常量2
      */
    private const FIZE1 = '1';

     /**
      * 测试常量3
      */
    protected const Fize2 = '2';

     /**
      * 测试常量FIZE3
      */
    public const FIZE3 = false;

     /**
      * 测试常量FIZE4
      */
    public const FIZE4 = [
        'name' => 'FizeChan',
        'sex'  => 30
    ];

     /**
      * 设置姓名，这里权重最大
      * @var string 姓名
      */
    private $name;

     /**
      * @var int 年龄
      */
    protected static $age = 31;

     /**
      * @var int 性别，0：未知；1-：男；2：女。
      */
    public $sex;

     /**
      * 通过使用该属性可以用于测试属性
      *
      * 测试属性测试属性测试属性测试属性测试属性
      * 测试属性测试属性测试属性测试属性测试属性
      * @var array 测试属性
      */
    protected $k2 = [
        'name' => '123',
        'ksal' => 123
    ];

     /**
      * 测试一下方法DOC注释
      * @param int $id ID
      * @param string $name 名称
      * @param int $age 年龄
      * @return int
      */
    public function edit($id, $name = 'fize', $age = 31)
    {
        echo $id;
        return $id;
    }

    public function add()
    {
        echo '测试add';
    }
}