<?php

/**
 * -----------| 工具类: 读取xml配置文件类 |-----------
 * @category Betterlife
 * @package util.config
 * @subpackage xml
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
class UtilConfigXml extends UtilConfig
{
    public function load($file)
    {
        if (file_exists($file) == false) {
            return false;
        }
        /**xmllib.php为PHP XML Library, version 1.2b,
         * 相关连接:http://keithdevens.com/software/phpxml
         * xmllib.php主要特点是把一个数组转换成一个xml或吧xml转换成一个数组
         * XML_unserialize:把一个xml给转换 成一个数组
         * XML_serialize:把一个数组转换成一个xml
         * 自PHP5起,simpleXML就很不错,但还是不支持将xml转换成数组的功能,所以xmlLIB还是很不错的.
         */
        $xml = file_get_contents($file);
        $this->_settings = UtilArray::xml_to_array($xml, "settings");
    }

    /**
    * 调用方法
    */
    public static function main()
    {
        // Load settings (XML)
        $settings = new UtilConfigXml();
        $settings->load(__DIR__ . DS . "setting.xml");
        echo 'XML: ' . $settings->get('db.host') . '';
    }
}
