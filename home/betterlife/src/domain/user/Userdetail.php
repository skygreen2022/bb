<?php

/**
 * -----------| 用户详细信息 |-----------
 * @category Betterlife
 * @package domain.user
 * @author skygreen skygreen2001@gmail.com
 */
class Userdetail extends DataObject
{
    //<editor-fold defaultstate="collapsed" desc="定义部分">
    /**
     * 标识
     * @var int
     * @access public
     */
    public $userdetail_id;
    /**
     * 用户标识
     * @var int
     * @access public
     */
    public $user_id;
    /**
     * 真实姓名
     * @var string
     * @access public
     */
    public $realname;
    /**
     * 头像
     *
     * 头像图片路径
     * @var string
     * @access public
     */
    public $profile;
    /**
     * 国家
     *
     * 参考region表的region_id字段
     * @var int
     * @access public
     */
    public $country;
    /**
     * 省
     *
     * 参考region表的region_id字段
     * @var int
     * @access public
     */
    public $province;
    /**
     * 市
     *
     * 参考region表的region_id字段
     * @var int
     * @access public
     */
    public $city;
    /**
     * 区
     *
     * 参考region表的region_id字段
     * @var int
     * @access public
     */
    public $district;
    /**
     * 家庭住址
     * @var string
     * @access public
     */
    public $address;
    /**
     * QQ号
     * @var string
     * @access public
     */
    public $qq;
    /**
     * 会员性别
     * - 0：女-female
     * - 1：男-male
     * - -1：待确认-unknown
     * - 默认男
     * @var enum
     * @access public
     */
    public $sex;
    /**
     * 生日
     * @var date
     * @access public
     */
    public $birthday;
    //</editor-fold>

    /**
     * 从属一对一关系
     * @var array
     */
    public static $belong_has_one = array(
        "user" => "User"
    );

    /**
     * 显示会员性别
     * - 0：女-female
     * - 1：男-male
     * - -1：待确认-unknown
     * - 默认男
     * @return string
     */
    public function getSexShow()
    {
        return self::sexShow($this->sex);
    }

    /**
     * 显示会员性别
     * - 0：女-female
     * - 1：男-male
     * - -1：待确认-unknown
     * - 默认男
     * @return string
     */
    public static function sexShow($sex)
    {
        return EnumSex::sexShow($sex);
    }
}
