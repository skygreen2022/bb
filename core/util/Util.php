<?php

/**
 * -----------| 所有工具类的父类 |-----------
 * @category Betterlife
 * @package util
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
class Util extends BBObject
{
    /**
     * xml单个element的属性值们。
     */
    const XML_ELEMENT_ATTRIBUTES = "attributes";
    /**
     * xml单个element的内容。
     */
    const XML_ELEMENT_TEXT = "text";

    /**
     * 垃圾回收，全称为Garbage Collection
     * @param mixed $value
     */
    public static function gc(&$value)
    {
        $value = null;
        unset($value);
    }
}
