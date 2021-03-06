<?php

/**
 * -----------| Sqlite的配置类 * Postgres的配置类 |-----------
 * @category Betterlife
 * @package core.config.db
 * @subpackage object
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
class ConfigSqlite extends ConfigDb
{
    /**
     * Sqlite 2 抓取数据的模式
     * SQLITE_ASSOC will return only associative indices (named fields)
     * SQLITE_NUM will return only numerical indices (ordinal field numbers)
     * SQLITE_BOTH will return both associative and numerical indices.
     * @link http://php.net/manual/en/sqlite.constants.php
     * @var enum
     */
    public static $sqlite2_fetchmode = SQLITE_ASSOC;//SQLITE_NUM|SQLITE_BOTH
    /**
     * Sqlite 3 抓取数据的模式
     * @link http://php.net/manual/en/sqlite3.constants.php
     * @var enum
     */
    public static $sqlite3_fetchmode = SQLITE3_ASSOC;
}
