<?php

/**
 * -----------| 所有数据库，表，列信息的父类 |-----------
 * @category Betterlife
 * @package core.db
 * @subpackage info
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
abstract class DbInfo extends BBObject
{
    protected $connection;//数据库连接

    /**
     * 构造器
     * @param string $host
     * @param string $port
     * @param string $username
     * @param string $password
     * @param string $dbname
     * @param mixed $engine 指定操作数据库引擎。{该字段的值参考: EnumDbEngine}
     */
    public function __construct($host = null, $port = null, $username = null, $password = null, $dbname = null, $engine = null)
    {
        $this->connect($host, $port, $username, $password, $dbname, $engine);
    }

    /**
     * 连接数据库
     * @param string $host
     * @param string $port
     * @param string $username
     * @param string $password
     * @param string $dbname
     * @param mixed $engine 指定操作数据库引擎。{该字段的值参考: EnumDbEngine}
     * @return mixed 数据库连接
     */
    abstract protected function connect($host = null, $port = null, $username = null, $password = null, $dbname = null, $engine = null);
}
