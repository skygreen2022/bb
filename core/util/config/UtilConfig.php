<?php

/**
 * -----------| 所有配置工具类的父类 |-----------
 *
 * 读取以下文件类型配置信息,
 *
 * 现支持php,ini,xml.yaml
 * @category Betterlife
 * @package util.config
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
class UtilConfig extends Util
{
    private static $config_xml  = 1;
    private static $config_ini  = 2;
    private static $config_yaml = 3;
    private static $config_php  = 4;
    private static $config_json = 5;
    private static $current;
    public static $config = 1;
    public $settings = array();

    public static function Instance()
    {
        switch (self::$config) {
            case self::$config_xml:
                self::$current = new UtilConfigXml();
                break;
            case self::$config_ini:
                self::$current = new UtilConfigIni();
                break;
            case self::$config_yaml:
                self::$current = new UtilConfigYaml();
                break;
            case self::$config_php:
                self::$current = new UtilConfigPhp();
                break;
            case self::$config_json:
                self::$current = new UtilConfigJson();
                break;
        }
        return self::$current;
    }

    /**
     * 获取某些设置的值
     * @param string $var
     * @return unknown
     */
    public function get($var)
    {
        $var    = explode('.', $var);
        $result = $this->_settings;
        foreach ($var as $key) {
            if (!isset($result[$key])) {
                return false;
            }
            $result = $result[$key];
        }
        return $result;
    }

    public function load($file)
    {
        trigger_error('Not yet implemented', E_USER_ERROR);
    }
}
