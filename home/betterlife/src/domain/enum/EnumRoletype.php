<?php

/**
 * -----------| 枚举类型:扮演角色 |-----------
 * @category Betterlife
 * @package domain
 * @subpackage enum
 * @author skygreen skygreen2001@gmail.com
 */
class EnumRoletype extends Enum
{
    /**
     * 扮演角色:超级管理员
     */
    const SUPERADMIN = '0';
    /**
     * 扮演角色:管理人员
     */
    const MANAGER = '1';
    /**
     * 扮演角色:运维人员
     */
    const NORMAL = '2';
    /**
     * 扮演角色:合作伙伴
     */
    const PARTNER = '3';

    /**
     * 显示扮演角色
     * - 系统管理员扮演角色。
     * - 0:超级管理员-superadmin
     * - 1:管理人员-manager
     * - 2:运维人员-normal
     * - 3:合作伙伴-partner
     * @return string
     */
    public static function roletypeShow($roletype)
    {
        switch ($roletype) {
            case self::SUPERADMIN:
                return "超级管理员";
            case self::MANAGER:
                return "管理人员";
            case self::NORMAL:
                return "运维人员";
            case self::PARTNER:
                return "合作伙伴";
        }
        return "未知";
    }

    /**
     * 根据扮演角色显示文字获取扮演角色
     * @param mixed $roletypeShow 扮演角色显示文字
     * @return string
     */
    public static function roletypeByShow($roletypeShow)
    {
        switch ($roletypeShow) {
            case "超级管理员":
                return self::SUPERADMIN;
            case "管理人员":
                return self::MANAGER;
            case "运维人员":
                return self::NORMAL;
            case "合作伙伴":
                return self::PARTNER;
        }
        return self::SUPERADMIN;
    }

    /**
     * 通过枚举值获取枚举键定义
     * @return string
     */
    public static function roletypeEnumKey($roletype)
    {
        switch ($roletype) {
            case '0':
                return "SUPERADMIN";
            case '1':
                return "MANAGER";
            case '2':
                return "NORMAL";
            case '3':
                return "PARTNER";
        }
        return "SUPERADMIN";
    }
}
