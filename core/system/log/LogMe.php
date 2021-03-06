<?php

/**
 * -----------| 日志处理类 |-----------
 *
 * 一般来讲，日志都通过log记录，由配置$logType决定它以什么方式打印出来。
 *
 * 除非明确指明用logConsole,logFile,logDb,logFirebug方式输出。
 *
 * @category Betterlife
 * @package core.system.log
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
class LogMe extends BBObject
{
    //<editor-fold defaultstate="collapsed" desc="日志核心代码">
    /**
     * 查看是否允许记录的日志级别。
     * @param type $level
     * @return type
     */
    private static function isNeedLog($level)
    {
        $logLevels = UtilReflection::getClassConsts("EnumLogLevel");
        if (in_array($level, $logLevels)) {
            $levelKey = array_search($level, $logLevels);
            if (in_array($levelKey, Gc::$log_config["log_record_level"])) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取日志文件路径。
     *
     * 前提条件: 采用文件方式记录日志。
     */
    public static function logPath($destination)
    {
        if (empty(Gc::$log_config["logpath"])) {
            Gc::$log_config["logpath"] = Gc::$nav_root_path . ConfigF::LOG_ROOT . DS;
        }
        if (!endWith(Gc::$log_config["logpath"], DS)) {
            Gc::$log_config["logpath"] .= DS;
        }
        if (empty($destination)) {
            $destination = Gc::$log_config["logpath"] . Gc::$appName . date('Y_m_d') . ConfigF::SUFFIX_FILE_LOG;
        } else {
            $destination = Gc::$log_config["logpath"] . $destination . date('Y_m_d') . ConfigF::SUFFIX_FILE_LOG;
        }

        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($destination) && (Gc::$log_config["logFile_size"] <= filesize($destination))) {
            rename($destination, dirname($destination) . DS . basename($destination, ConfigF::SUFFIX_FILE_LOG) . '-' . time() . ConfigF::SUFFIX_FILE_LOG);
        }
        if (isset($destination)) {
            UtilFileSystem::createDir(dirname($destination));
        }
        system_dir_info(dirname($destination));
        return $destination;
    }

    /**
     * 记录日志
     * @param string $message 日志记录的内容
     * @param enum $level 日志记录级别
     * @param string $category 日志内容业务分类
     */
    public static function log($message, $level = EnumLogLevel::INFO, $category = '')
    {
        if (self::isNeedLog($level)) {
            switch (Gc::$log_config["logType"]) {
                case EnumLogType::SYSTEM:
                    self::logConsole($message, $level, $category);
                    break;
                case EnumLogType::BROWSER:
                    self::logBrowser($message, $level, $category);
                    break;
                case EnumLogType::FILE:
                    self::logFile($message, $level, $category);
                    break;
                case EnumLogType::MAIL:
                    self::logEmail($message, $level, $category);
                    break;
                case EnumLogType::DB:
                    self::logDb($message, $level, $category);
                    break;
                case EnumLogType::FIREBUG:
                    self::logFirebug($message, $level, $category);
                    break;
                default:
                    self::record($message, $level);
                    break;
            }
        }
    }

    /**
     * 记录系统日志，直接在浏览器显示出来
     * @param string $message 日志记录的内容
     * @param enum $level 日志记录级别
     * @param string $category 日志内容业务分类
     */
    public static function logConsole($message, $level = EnumLogLevel::INFO, $category = "")
    {
        UtilDateTime::ChinaTime();
        $conf = array('timeFormat' => Gc::$log_config["timeFormat"]);
        Log::singleton('console', '', $category, $conf)->log($message, $level);
    }

    /**
     * 记录系统日志，直接在浏览器显示出来
     * @param string $message 日志记录的内容
     * @param enum $level 日志记录级别
     * @param string $category 日志内容业务分类
     */
    public static function logBrowser($message, $level = EnumLogLevel::INFO, $category = "")
    {
        echo $message . "";
    }

    /**
     * 记录日志，在文件里记录日志
     * @param string $message 日志记录的内容
     * @param enum $level 日志记录级别
     * @param string $category 日志内容业务分类
     */
    public static function logFile($message, $level = EnumLogLevel::INFO, $category = "")
    {
        UtilDateTime::ChinaTime();
        $conf = array('timeFormat' => Gc::$log_config["timeFormat"]);
        Log::singleton('file', self::logPath($category), $category, $conf)->log($message, $level);
    }

    /**
     * 记录日志，通过邮件发送重要的通知日志
     * @param string $message 日志记录的内容
     * @param enum $level 日志记录级别
     * @param string $category 日志内容业务分类
     */
    public static function logEmail($message, $level = EnumLogLevel::EMERG, $category = "")
    {
        UtilDateTime::ChinaTime();
        Gc::$log_config["config_mail_log"]["timeFormat"] = Gc::$log_config["timeFormat"];
        Log::singleton('mail', Gc::$email_config["mailto"], $category, Gc::$log_config["config_mail_log"])->log($message, $level);
    }

    /**
     * 记录日志，在数据库文件里记录日志内容
     * @param string $message 日志记录的内容
     * @param enum $level 日志记录级别
     * @param string $category 日志内容业务分类
     */
    public static function logDb($message, $level = EnumLogLevel::INFO, $category = "")
    {
        UtilDateTime::ChinaTime();
        $conf = array('timeFormat' => Gc::$log_config["timeFormat"]);
        $conf = array('dsn' => ConfigMdb2::dsn());
        Log::singleton('sql', Gc::$log_config["log_table"], $category, $conf)->log($message, $level);
    }

    /**
     * 记录日志，通过Firfox的Firebug插件的控制台打印出日志来
     * @param string $message 日志记录的内容
     * @param enum $level 日志记录级别
     * @param string $category 日志内容业务分类
     */
    public static function logFirebug($message, $level = EnumLogLevel::INFO, $category = "")
    {
        UtilDateTime::ChinaTime();
        $conf = array('timeFormat' => Gc::$log_config["timeFormat"]);
        Log::singleton('firebug', '', $category, $conf)->log($message, $level);
    }
    //</editor-fold>

    //<editor-fold defaultstate="collapsed" desc="实时调试显示日志内容">
    /**
     * 日志信息
     * @var array
     * @access private
     * @static
     */
    private static $log =   array();

    /**
     * 记录日志 并且会过滤未经设置的级别
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param boolean $record  是否强制记录保存在文件里
     * @return void
     */
    public static function record($message, $level = EnumLogLevel::ERR, $record = false)
    {
        if (Gc::$dev_debug_on) {
            if ($record || self::isNeedLog($level)) {
                UtilDateTime::ChinaTime();
                $now = "[ " . strftime(Gc::$log_config["timeFormat"]) . " ]";
                //UtilReflection::getClassProperty(EnumLogLevel, $level);
                $level = UtilReflection::getClassConstNameByValue("EnumLogLevel", $level);
                self::$log[] = "{$now} {$level}: {$message}" . HH;
            }
        }
    }

    /**
     * 日志保存
     * @static
     * @access public
     * @param string $destination  写入目标
     * @param string $extra 额外参数
     * @return void
     */
    private static function save($destination = '', $extra = '', $type = EnumLogType::FILE)
    {
        Gc::$log_config["logpath"] = self::logPath($destination);
        error_log(implode("", self::$log), $type, Gc::$log_config["logpath"], $extra);
        // 保存后清空日志缓存
        self::$log = array();
        clearstatcache();
    }

    /**
     * 显示当前运行的日志。
     */
    public static function showLogs()
    {
        if (Gc::$dev_debug_on) {
            if (self::$log) {
                foreach (self::$log as $log) {
                    echo '<pre>';
                    echo $log;
                    echo '</pre>';
                }
                self::save();
                echo "您可以在日志文件中找到信息: " . Gc::$log_config["logpath"];
            }
        }
    }
    //</editor-fold>
}
