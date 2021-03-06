<?php

/**
 * -----------| 返回数据类型 |-----------
 */
class EnumReturnType extends Enum
{
    /**
     * 数据对象
     */
    const DATAOBJECT = 0;
    /**
     * 数组
     */
    const ARRAYTYPE = 1;
}

/**
 * -----------| 所有对象的基类 |-----------
 * @category Betterlife
 * @package core
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
abstract class BBObject
{
    /***********************************魔术方法**************************************************/
    /**
     * 自动变量设置
     * @access public
     * @param $name 属性名称
     * @param $value  属性值
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    /**
     * 自动变量获取
     * @access public
     * @param $name 属性名称
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }

    /***********************************魔术方法**************************************************/
    /**
     * 获取被调用类的类名
     *
     */
    public function classname()
    {
        return get_class($this);
    }
}
