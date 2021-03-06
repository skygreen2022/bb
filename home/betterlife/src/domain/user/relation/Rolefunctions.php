<?php

/**
 * -----------| 角色拥有功能角色拥有功能 |-----------
 * @category Betterlife
 * @package domain.user.relation
 * @author skygreen skygreen2001@gmail.com
 */
class Rolefunctions extends DataObject
{
    //<editor-fold defaultstate="collapsed" desc="定义部分">
    /**
     * 标识
     * @var int
     * @access public
     */
    public $rolefunctions_id;
    /**
     * 角色标识
     * @var int
     * @access public
     */
    public $role_id;
    /**
     * 功能标识
     * @var int
     * @access public
     */
    public $functions_id;
    //</editor-fold>

    /**
     * 从属一对一关系
     * @var array
     */
    public static $belong_has_one = array(
        "role" => "Role",
        "functions" => "Functions"
    );
    /**
     * 规格说明
     *
     * 表中不存在的默认列定义: commitTime, updateTime
     *
     * @var mixed
     */
    public $field_spec = array(
        EnumDataSpec::REMOVE => array(
            'commitTime',
            'updateTime'
        ),
    );
}
