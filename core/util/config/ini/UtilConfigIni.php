<?php

/**
 * -----------| 工具类: 读取Ini配置文件类 |-----------
 * @category Betterlife
 * @package util.config
 * @subpackage ini
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
class UtilConfigIni extends UtilConfig
{
    public function load($file)
    {
        if (file_exists($file) == false) {
            return false;
        }
        $this->_settings = parse_ini_file($file, true);
    }

    /**
     * 调用方法
     */
    public static function main()
    {
        $settings = new UtilConfigIni();
        $settings->load(__DIR__ . DS . 'setting.ini');
        echo 'INI: ' . $settings->get('db.host') . '';
    }
}
