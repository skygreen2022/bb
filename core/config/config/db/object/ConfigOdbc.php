<?php

/**
 * -----------| Microsoft ODBC方案 的配置类 |-----------
 * @category Betterlife
 * @package core.config.db
 * @subpackage object
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
class ConfigOdbc extends ConfigDb
{
    /**
     * 返回ODBC所需的dsn_less字符串
     *
     * 说明:
     *
     *    $dsn可以直接在System DSN里配置；然后在配置里设置: ConfigDb::$dbname
     * @param string $host
     * @param string $dbname
     * @param enum $dbtype 指定数据库类型。{该字段的值参考: EnumDbSource}
     * @return string ODBC所需的dsn_less字符串
     */
    public static function dsn_less($host = null, $username = null, $password = null, $dbname = null, $dbtype = null)
    {
        switch ($dbtype) {
            case EnumDbSource::DB_MICROSOFT_ACCESS:
                $dsn_less = "Driver={Microsoft Access Driver (*.mdb)};Dbq=" . $dbname;
                break;
            case EnumDbSource::DB_SQLSERVER:
                $dsn_less = "Driver={SQL Server Native Client 10.0};Server=" . $host . ";Database=" . $dbname . ";";
                break;
            case EnumDbSource::DB_MICROSOFT_EXCEL:
                $excelFile = realpath($dbname);
                $excelDir  = dirname($excelFile);
                $dsn_less  = "Driver={Microsoft Excel Driver (*.xls)};DriverId=790;Dbq=" . $excelFile . ";DefaultDir=" . $excelDir;
                break;
        }
        return $dsn_less;
    }

    /**
     * 返回ODBC所需的dsn字符串
     * @param string $dbname 数据库名称
     * @return string dsn ODBC所需的dsn字符串
     */
    public static function dsn($dbname = null)
    {
        if (isset($dbname)) {
            return $dbname;
        } else {
            return ConfigDb::$dbname;
        }
    }
}
