<?php

/**
 * 使用JS框架
 */
class EnumJsFramework extends Enum
{
    const JS_FW_JQUERY        = "jquery";
    const JS_FW_PROTOTYPE     = "prototype";
    const JS_FW_YUI           = "yui";
    const JS_FW_MOOTOOLS      = "mootools";
    const JS_FW_EXTJS         = "extjs";
    const JS_FW_DOJO          = "dojo";
    const JS_FW_SCRIPTACULOUS = "scriptaculous";
    const JS_FW_PROTACULOUS   = "protaculous";
}

/**
 * 请求响应的数据类型
 */
class EnumResponseType extends Enum
{
    const JSON       = "json";
    const XML        = "xml";
    const HTML       = "html";
    const JSONP      = "jsonp";
    const TEXT       = "text";
    const SERIALIZED = "serialized";
}

/**
 * HTTP的标准协议动词
 */
class EnumHttpMethod extends Enum
{
    /**
     * 即常见的url Get方式。
     */
    const GET     = "GET";
    /**
     * 即常见的url Post方式。
     */
    const POST    = "POST";
    /**
     * PUT Restful中更新数据
     */
    const PUT     = "PUT";
    /**
     * DELETE Restful中删除数据
     */
    const DELETE  = "DELETE";
    /**
     * 暂未实现启用
     */
    const OPTIONS = "OPTIONS";
}


/**
 * -----------| 所有Javascript Ajax 框架的工具类的父类 |-----------
 * @category Betterlife
 * @package util.view.ajax
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
class UtilAjax extends Util
{
    //<editor-fold defaultstate="collapsed" desc="定义部分">
    /**
     * 对JS进行Gzip操作的路径
     * @var string
     */
    protected static $JS_GZIP              = "misc/js/gzip.php?js=";
    /**
     * JS框架名称键名称
     * @var string
     */
    protected static $JS_FLAG_GROUP        = "g";
    /**
     * JS框架版本键名称
     * @var string
     */
    protected static $JS_FLAG_VERSION      = "v";
    /**
     * 默认使用的Ajax框架名称
     * @var string
     */
    public static $ajax_fw_name_default    = EnumJsFramework::JS_FW_JQUERY;//JS_FW_JQUERY
    /**
     * 默认使用的Ajax框架版本
     * @var string
     */
    public static $ajax_fw_version_default = "3.2.1"; //1.6.1
    /**
     * 推荐的Ajax框架和可使用的版本。
     *
     * 选用Ajax框架和可使用的版本可参考该列表
     *
     * @link http://code.google.com/intl/zh-CN/apis/libraries/devguide.html
     * @var array
     */
    public static $ajax_fw_list = array(
        "jquery"    => "3.2.1",   //JQuery可使用1.5.0以上版本。
        "prototype" => "1.7.0.0", //Prototype可使用1.6.0.2以上版本。
        "yui"       => "3.3.0",   //YUI可使用3.3.0以上版本。
        "mootools"  => "1.3.2",   //Mootools可使用1.3.2以上版本。
        "dojo"      => "1.6.0",   //Dojo可使用1.6.0以上版本。
    );

    /**
     * 是否采用google library api加载Ajax库。
     *
     * @link http://code.google.com/intl/zh-CN/apis/libraries/devguide.html
     * @var bool
     */
    public static $IsGoogleApi = false;
    /**
     * 加载过的Js文件。
     * $value Js文件名
     * @var array
     */
    public static $JsLoaded    = array();
    /**
     * 回调函数的内容会显示在页面上，当一次调用多个Ajax请求时，因此只需要写一次<html><body>。
     *
     * 该状态记录是否已经显示出过<html><body>
     * @var bool
     */
    public static $IsHtmlBody  = false;
    /**
     * 是否允许调试
     *
     * @var mixed
     */
    public static $IsDebug     = true;
    //</editor-fold>
    /**
     * 初始化方能加载枚举类型。
     */
    public static function init()
    {
    }

    /**
     * @return string 当前类名
     */
    public static function name()
    {
        return __CLASS__;
    }

    /**
     * 加载默认的Ajax框架
     */
    public static function loadDefaultAjax()
    {
        $version       = self::$ajax_fw_version_default;
        $loadJsLibrary = self::name() . ucfirst(self::$ajax_fw_name_default);
        $viewObject    = new ViewObject();
        call_user_func("$loadJsLibrary::load", $version, $viewObject);
        $result        = $viewObject->js_ready;
        return $result;
    }

    /**
     * 动态加载Ajax Javascript Framework库
     * @param string $jsFlag Ajax Javascript Framework 标识
     * @param string $version javascript框架的版本号
     * @param ViewObject $viewObject 表示层显示对象,只在Web框架中使用,一般结合loadJsReady使用
     */
    public static function loadAjaxJs($jsFlag, $version = "", $viewobject = null)
    {
        if ($viewobject) {
            self::loadJsReady($viewobject, "", true, $jsFlag, $version);
        } else {
            echo self::loadJsSentence("", true, $jsFlag, $version);
        }
    }

    /**
     * 动态加载应用指定的Js文件。
     *
     * 可通过分组标识动态加载Ajax Javascript Framework库
     *
     * @param string $jsFile: 相对网站的根目录的Javascript文件名相对路径
     * @param bool $isGzip 是否使用Gzip进行压缩。
     * @param string $jsFlag Ajax Javascript Framework 标识
     * @param string $version javascript框架的版本号
     * @param ViewObject $viewobject 表示层显示对象,只在Web框架中使用,一般结合loadJsReady使用
     */
    public static function loadJs($jsFile, $isGzip = false, $jsFlag = null, $version = "", $viewobject = null)
    {
        if ($viewobject) {
            self::loadJsReady($viewobject, $jsFile, $isGzip, $jsFlag, $version);
        } else {
            echo self::loadJsSentence($jsFile, $isGzip, $jsFlag, $version);
        }
    }

    /**
     * 预加载[不直接输出]:动态加载应用指定的Js文件。
     *
     * 可通过分组标识动态加载Ajax Javascript Framework库
     *
     * @param ViewObject $viewobject 表示层显示对象,只在Web框架中使用,一般结合loadJsReady使用
     * @param string $jsFile: 相对网站的根目录的Javascript文件名相对路径
     * @param bool $isGzip 是否使用Gzip进行压缩。
     * @param string $jsFlag Ajax Javascript Framework 标识
     * @param string $version javascript框架的版本号
     */
    public static function loadJsReady($viewobject, $jsFile, $isGzip = false, $jsFlag = null, $version = "")
    {
        if ($viewobject instanceof ViewObject) {
            if (!isset($viewobject->js_ready) || empty($viewobject->js_ready)) {
                $viewobject->js_ready = "";
            }
            $viewobject->js_ready .= self::loadJsSentence($jsFile, $isGzip, $jsFlag, $version);
        }
    }

    /**
     * 动态加载应用指定的Js文件的语句。
     *
     * 可通过分组标识动态加载Ajax Javascript Framework库
     *
     * @param string $jsFile  相对网站的根目录的Javascript文件名相对路径
     * @param bool $isGzip    是否使用Gzip进行压缩。
     * @param string $jsFlag  Ajax Javascript Framework 标识
     * @param string $version javascript框架的版本号
     */
    public static function loadJsSentence($jsFile, $isGzip = false, $jsFlag = null, $version = "")
    {
        $result = "";
        if (isset($jsFile)) {
            $url_base = UtilNet::urlbase();
            if ($isGzip) {
                if (isset($jsFlag)) {
                    $jsFile .= "&" . self::$JS_FLAG_GROUP . "=" . $jsFlag;
                }
                if (!empty($version)) {
                    $jsFile .= "&" . self::$JS_FLAG_VERSION . "=" . $version;
                }
                if (in_array($jsFile, self::$JsLoaded)) {
                    return ;
                }
                $file_sub_dir = str_replace("/", DS, dirname($_SERVER["SCRIPT_FILENAME"])) . DS;
                if (contain(Gc::$nav_root_path, "/mnt/") && contain($file_sub_dir, "/var/")) {
                    $file_sub_dir = str_replace("/var/", "/mnt/", $file_sub_dir);
                }
                if (contain($file_sub_dir, Gc::$nav_root_path)) {
                    $result = "    <script type=\"text/javascript\" src=\"" . $url_base . self::$JS_GZIP . "{$jsFile}\"></script>" . HH;
                } else {
                    $isLocalJsFile = str_replace(Gc::$url_base, $file_sub_dir, $jsFile);
                    if (contain($isLocalJsFile, "home" . DS)) {
                        $isLocalJsFile = substr($isLocalJsFile, 0, strpos($isLocalJsFile, "home" . DS));
                    }
                    $js_gzip = str_replace($_SERVER["DOCUMENT_ROOT"], "", $isLocalJsFile);
                    $js_gzip = str_replace("\\", "/", $js_gzip);

                    if (contain(strtolower(php_uname()), "darwin")) {
                        $js_gzip   = str_replace($_SERVER["DOCUMENT_ROOT"] . "/", "", $file_sub_dir);

                        $start_str = substr($js_gzip, 0, strpos($js_gzip, "/"));
                        $url_basei = substr($url_base, 0, strlen($url_base) - 1);
                        $end_str   = substr($url_basei, strrpos($url_basei, "/") + 1);
                        if ($start_str == $end_str) {
                            $js_gzip = str_replace($end_str . "/", "", $js_gzip);
                        }
                    }

                    $result = "    <script type=\"text/javascript\" src=\"" . $url_base . $js_gzip . self::$JS_GZIP . "{$jsFile}\"></script>" . HH;
                }
            } else {
                if (in_array($jsFile, self::$JsLoaded)) {
                    return ;
                }
                if (startWith($jsFile, "http")) {
                    $result = "    <script type=\"text/javascript\" src=\"" . $jsFile . "\"></script>" . HH;
                } else {
                    if (contain(strtolower(php_uname()), "darwin")) {
                        $file_sub_dir = str_replace("/", DS, dirname($_SERVER["SCRIPT_FILENAME"])) . DS;
                        $jsFile       = str_replace($_SERVER["DOCUMENT_ROOT"] . "/", "", $file_sub_dir) . $jsFile;

                        $start_str = substr($jsFile, 0, strpos($jsFile, "/"));
                        $url_basei = substr($url_base, 0, strlen($url_base) - 1);
                        $end_str   = substr($url_basei, strrpos($url_basei, "/") + 1);
                        if ($start_str == $end_str) {
                            $jsFile = str_replace($end_str . "/", "", $jsFile);
                        }
                    }
                    $result = "    <script type=\"text/javascript\" src=\"" . $url_base . $jsFile . "\"></script>" . HH;
                }
            }
            self::$JsLoaded[] = $jsFile;
        }
        return $result;
    }

    /**
     * 动态加载应用指定的Js内容的语句。
     * @param string $jsContent: Js内容的语句
     */
    public static function loadJsContent($jsContent)
    {
        echo self::loadJsContentSentence($jsContent);
    }

    /**
     * 预加载[不直接输出]:动态加载应用指定的Js内容的语句。
     * @param ViewObject $viewobject 表示层显示对象,只在Web框架中使用,一般结合loadJsReady使用
     * @param string $jsContent: Js内容的语句
     */
    public static function loadJsContentReady($viewobject, $jsContent)
    {
        if ($viewobject instanceof ViewObject) {
            if (!isset($viewobject->js_ready) || empty($viewobject->js_ready)) {
                $viewobject->js_ready = "";
            }
            $viewobject->js_ready .= self::loadJsContentSentence($jsContent);
        }
    }

    /**
     * 动态加载应用指定的Js内容的语句。
     * @param string $jsContent: Js内容的语句
     */
    public static function loadJsContentSentence($jsContent)
    {
        if (!contain($jsContent, "<script")) {
            $result  = "    <script type=\"text/javascript\">" . HH;
            $result .= "        " . $jsContent . HH;
            $result .= "    </script>" . HH;
        } else {
            $result  = $jsContent;
        }
        return $result;
    }
}
