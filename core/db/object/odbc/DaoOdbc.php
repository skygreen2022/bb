<?php

/**
 * -----------| 主要用于微软的ODBC数据库方案 |-----------
 *
 * 主要适用于Microsoft Accesss
 * @link http://www.w3schools.com/php/php_db_odbc.asp
 * @category Betterlife
 * @package core.db.object
 * @subpackage odbc
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
class DaoOdbc extends Dao implements IDaoNormal
{
    /**
     * @param enum $dbtype 指定数据库类型。{使用DaoODBC引擎，需要定义该字段,该字段的值参考:EnumDbSource}
     */
    private $dbtype;

    /**
     * 指定数据库类型
     * @param enum $dbtype
     */
    public function setdbType($dbtype)
    {
        $this->dbtype = $dbtype;
    }

    /**
     * 连接数据库
     *
     * 说明:$dsn可以直接在System DSN里配置；然后在配置里设置:ConfigDb::$dbname
     *
     * @param string $host
     * @param string $port
     * @param string $username
     * @param string $password
     * @param string $dbname
     * @param enum $dbtype 指定数据库类型。{使用DaoODBC引擎，需要定义该字段,该字段的值参考:EnumDbSource}
     * @return mixed 数据库连接
     */
    public function connect($host = null, $port = null, $username = null, $password = null, $dbname = null)
    {
        if (!isset($host)) {
            $host     = ConfigOdbc::$host;
        }
        if (!isset($username)) {
            $username = ConfigOdbc::$username;
        }
        if (!isset($password)) {
            $password = ConfigOdbc::$password;
        }
        if (!isset($dbname)) {
            $dbname   = ConfigOdbc::$dbname;
        }
        if (!isset($this->dbtype)) {
            $dbtype    = ConfigOdbc::$db;
        }
        if (ConfigDb::$is_dsn_set) {
            $this->connection = odbc_connect(ConfigOdbc::dsn($dbname), $username, $password);
        } else {
            if (ConfigOdbc::$is_persistent) {
                $this->connection = odbc_pconnect(ConfigOdbc::dsn_less($host, $dbname, $dbtype), $username, $password);
            } else {
                $this->connection = odbc_connect(ConfigOdbc::dsn_less($host, $dbname, $dbtype), $username, $password);
            }
        }
        if (!$this->connection) {
            ExceptionDb::record(Wl::ERROR_INFO_CONNECT_FAIL);
        }
    }

    /**
     * 新建对象
     * @param Object $object
     * @return int 保存对象记录的ID标识号
     */
    public function save($object)
    {
        $autoId = -1;//新建对象插入数据库记录失败
        if (!$this->validObjectParameter($object)) {
            return $autoId;
        }
        try {
            $_SQL = new CrudSqlInsert();
            $object->setCommitTime(UtilDateTime::now(EnumDateTimeFormat::TIMESTAMP));
            if (
                ConfigDb::$db == EnumDbSource::DB_SQLSERVER &&
                    ( ( trim(strtoupper(Gc::$encoding)) == ConfigC::CHARACTER_UTF_8 ) || ( trim(strtolower(Gc::$encoding)) == ConfigC::CHARACTER_UTF8))
            ) {
                $this->saParams = UtilObject::object_to_array($object, false, array(ConfigC::CHARACTER_UTF_8 => ConfigC::CHARACTER_GBK));
            } else {
                $this->saParams = UtilObject::object_to_array($object);
            }
            $this->sQuery = $_SQL->insert($this->classname)->values($this->saParams)->result();
            $_SQL->isPreparedStatement = true;
            if (ConfigDb::$debug_show_sql) {
                LogMe::log("SQL: " . $this->sQuery);
                if (!empty($this->saParams)) {
                    LogMe::log("SQL PARAM: " . var_export($this->saParams, true));
                }
            }
            $this->stmt = odbc_prepare($this->connection, $this->sQuery);
            $success = odbc_execute($this->stmt, array_values($this->saParams));
            if (!$success) {
                ExceptionDb::log(Wl::ERROR_INFO_DB_HANDLE);
            }

            $realIdName = DataObjectSpec::getRealIDColumnName($object);
            $sql_maxid  = CrudSQL::SQL_MAXID;
            $sql_maxid  = str_replace(CrudSQL::SQL_FLAG_ID, $realIdName, $sql_maxid);

            $tablename  = ConfigOdbc::orm($this->classname);
            $autoIdSql  = CrudSQL::SQL_SELECT . $sql_maxid . CrudSQL::SQL_FROM . $tablename;
            if (ConfigDb::$debug_show_sql) {
                LogMe::log("SQL: " . $autoIdSql);
            }
            $this->stmt = odbc_exec($this->connection, $autoIdSql);
            $autoId     = odbc_result($this->stmt, 1);
        } catch (Exception $exc) {
            ExceptionDb::log($exc->getMessage() . "" . $exc->getTraceAsString());
        }
        if (!empty($object) && is_object($object)) {
            $object->setId($autoId);//当保存返回对象时使用
        }
        return $autoId;
    }

    /**
     * 删除对象
     * @param string $classname
     * @param int $id
     * @return boolean
     */
    public function delete($object)
    {
        $result = false;
        if (!$this->validObjectParameter($object)) {
            return $result;
        }
        $id = $object->getId();
        if (!empty($id)) {
            try {
                $_SQL         = new CrudSqlDelete();
                $where        = $this->sql_id($object) . self::EQUAL . $id;
                $this->sQuery = $_SQL->deletefrom($this->classname)->where($where)->result();
                if (ConfigDb::$debug_show_sql) {
                    LogMe::log("SQL: " . $this->sQuery);
                }
                odbc_exec($this->connection, $this->sQuery);
                $result = true;
            } catch (Exception $exc) {
                ExceptionDb::log($exc->getTraceAsString());
            }
        }
        return $result;
    }

    /**
     * 更新对象
     * @param object $object
     * @return boolean
     */
    public function update($object)
    {
        $result = false;
        if (!$this->validObjectParameter($object)) {
            return $result;
        }

        $id = $object->getId();
        if (!empty($id)) {
            try {
                $_SQL = new CrudSqlUpdate();
                $_SQL->isPreparedStatement = false;
                $object->setUpdateTime(UtilDateTime::now(EnumDateTimeFormat::STRING));
                if (
                    ConfigDb::$db == EnumDbSource::DB_SQLSERVER &&
                        ( ( trim(strtoupper(Gc::$encoding)) == ConfigC::CHARACTER_UTF_8 ) || ( trim(strtolower(Gc::$encoding)) == ConfigC::CHARACTER_UTF8))
                ) {
                    $this->saParams = UtilObject::object_to_array($object, false, array(ConfigC::CHARACTER_UTF_8 => ConfigC::CHARACTER_GBK));
                } else {
                    $this->saParams = UtilObject::object_to_array($object);
                }
                unset($this->saParams[DataObjectSpec::getRealIDColumnName($object)]);
                $this->saParams = $this->filterViewProperties($this->saParams);
                $where          = $this->sql_id($object) . self::EQUAL . $id;
                $this->sQuery   = $_SQL->update($this->classname)->set($this->saParams)->where($where)->result();
                if (ConfigDb::$debug_show_sql) {
                    LogMe::log("SQL: " . $this->sQuery);
                    if (!empty($this->saParams)) {
                        LogMe::log("SQL PARAM: " . var_export($this->saParams, true));
                    }
                }
                odbc_exec($this->connection, $this->sQuery);
                $result = true;
            } catch (Exception $exc) {
                ExceptionDb::log($exc->getTraceAsString());
                $result = false;
            }
        } else {
            x(Wl::ERROR_INFO_UPDATE_ID, $this);
        }
        return $result;
    }

    /**
     * 保存或更新当前对象
     * @param Object $dataobject
     * @return boolen|int 更新:是否更新成功；true为操作正常|保存:保存对象记录的ID标识号
     */
    public function saveOrUpdate($dataobject)
    {
        $id = $dataobject->getId();
        if (isset($id)) {
            $result = $this->update($dataobject);
        } else {
            $result = $this->save($dataobject);
        }
        return $result;
    }

    /**
     * 将查询结果转换成业务层所认知的对象
     * @param string $object 需要转换成的对象实体|类名称
     * @return mixed 转换成的对象实体列表
     */
    private function getResultToObjects($object)
    {
        $rows = array();
        $row  = array();
//        $odbc_num=odbc_num_rows($this->stmt);
        while (odbc_fetch_into($this->stmt, $row)) {
            array_push($rows, $row);
        }

        $fieldCount  = odbc_num_fields($this->stmt);
        $columnNames = array();
        for ($i = 1; $i <= $fieldCount; $i++) {
            $columnNames[] = odbc_field_name($this->stmt, $i);
        }
        $result = array();
        foreach ($rows as $row) {
            $row = array_combine($columnNames, $row);
            if (!empty($object)) {
                if ($this->validParameter($object)) {
                    $c = UtilObject::array_to_object($row, $this->classname);
                    $result[] = $c;
                }
            } else {
                if (count($row) == 1) {
                    foreach ($row as $key => $val) {
                        $result[] = $val;
                    }
                } else {
                    $c = new stdClass();
                    foreach ($row as $key => $val) {
                        $c->{$key} = $val;
                    }
                    $result[] = $c;
                }
            }
        }
        $result = $this->getValueIfOneValue($result);
        return $result;
    }

    /**
     * 根据对象实体查询对象列表
     *
     * @param string $object 需要查询的对象实体|类名称
     * @param string $filter 查询条件，在where后的条件
     * 示例如下:
     *
     *     0. "id=1,name='sky'"
     *     1. array("id=1","name='sky'")
     *     2. array("id"=>"1","name"=>"sky")
     *     3. 允许对象如new User(id="1",name="green");
     *
     * 默认:SQL Where条件子语句。如:(id=1 and name='sky') or (name like 'sky')
     *
     * @param string $sort 排序条件
     * 示例如下:
     *
     *     1. id asc;
     *     2. name desc;
     *
     * @param string $limit 分页数目:同Mysql limit语法
     * 示例如下:
     *
     *     0,10
     *
     * @return array 对象列表数组
     */
    public function get($object, $filter = null, $sort = CrudSQL::SQL_ORDER_DEFAULT_ID, $limit = null)
    {
        $result = null;
        try {
            if (!$this->validParameter($object)) {
                return $result;
            }
            $_SQL = new CrudSqlSelect();
            if ($sort == CrudSQL::SQL_ORDER_DEFAULT_ID) {
                $realIdName = $this->sql_id($object);
                $sort       = str_replace(CrudSQL::SQL_FLAG_ID, $realIdName, $sort);
            }
            $_SQL->isPreparedStatement = true;
            $this->saParams            = $_SQL->parseValidInputParam($filter);
            $_SQL->isPreparedStatement = false;
            $this->sQuery              = $_SQL->select()->from($this->classname)->where($this->saParams)->order($sort)->limit($limit)->result();
            if (ConfigDb::$debug_show_sql) {
                LogMe::log("SQL: " . $this->sQuery);
                if (!empty($this->saParams)) {
                    LogMe::log("SQL PARAM: " . var_export($this->saParams, true));
                }
            }
            $this->stmt = odbc_exec($this->connection, $this->sQuery);
            $result     = $this->getResultToObjects($object);
            return $result;
        } catch (Exception $exc) {
            ExceptionDb::record($exc->getTraceAsString());
        }
    }

    /**
     * 查询得到单个对象实体
     *
     * @param string|class $object 需要查询的对象实体|类名称
     * @param object|string|array $filter 查询条件，在where后的条件
     * 示例如下:
     *
     *     0. "id=1,name='sky'"
     *     1. array("id=1","name='sky'")
     *     2. array("id"=>"1","name"=>"sky")
     *     3. 允许对象如new User(id="1",name="green");
     *
     * 默认:SQL Where条件子语句。如:(id=1 and name='sky') or (name like 'sky')
     *
     * @param string $sort 排序条件
     * 示例如下:
     *
     *     1. id asc;
     *     2. name desc;
     *
     * @return object 单个对象实体
     */
    public function getOne($object, $filter = null, $sort = CrudSQL::SQL_ORDER_DEFAULT_ID)
    {
        $result = null;
        try {
            if (!$this->validParameter($object)) {
                return $result;
            }

            $_SQL = new CrudSqlSelect();
            $_SQL->isPreparedStatement = true;
            $this->saParams            = $_SQL->parseValidInputParam($filter);
            $_SQL->isPreparedStatement = false;
            if ($sort == CrudSQL::SQL_ORDER_DEFAULT_ID) {
                $realIdName = $this->sql_id($object);
                $sort       = str_replace(CrudSQL::SQL_FLAG_ID, $realIdName, $sort);
            }
            $this->sQuery = $_SQL->select()->from($this->classname)->where($this->saParams)->order($sort)->result();
            if (ConfigDb::$debug_show_sql) {
                LogMe::log("SQL: " . $this->sQuery);
                if (!empty($this->saParams)) {
                    LogMe::log("SQL PARAM: " . var_export($this->saParams, true));
                }
            }
            $this->stmt = odbc_exec($this->connection, $this->sQuery);
            $result     = $this->getResultToObjects($object);
            if (count($result) >= 1) {
                $result = $result[0];
            }
            return $result;
        } catch (Exception $exc) {
            ExceptionDb::record($exc->getTraceAsString());
        }
    }

    /**
     * 根据表ID主键获取指定的对象[ID对应的表列]
     * @param string $classname
     * @param string $id
     * @return object 对象
     */
    public function getById($object, $id)
    {
        $result = null;
        try {
            if (!$this->validParameter($object)) {
                return $result;
            }

            if (!empty($id) && is_scalar($id)) {
                $_SQL  = new CrudSqlSelect();
                $where = $this->sql_id($object) . self::EQUAL . $id;
                $this->saParams = null;
                $this->sQuery   = $_SQL->select()->from($this->classname)->where($where)->result();
                if (ConfigDb::$debug_show_sql) {
                    LogMe::log("SQL: " . $this->sQuery);
                }
                $this->stmt = odbc_exec($this->connection, $this->sQuery);
                $result     = $this->getResultToObjects($object);
                if (count($result) == 1) {
                    $result = $result[0];
                }
                return $result;
            }
        } catch (Exception $exc) {
            ExceptionDb::record($exc->getTraceAsString());
        }
    }

    /**
     *  直接执行SQL语句
     *
     * @param mixed $sql SQL查询语句
     * @param string|class $object 需要生成注入的对象实体|类名称
     * @return array 返回数组
     */
    public function sqlExecute($sqlstring, $object = null)
    {
        $result = null;
        try {
            if (ConfigDb::$db == EnumDbSource::DB_SQLSERVER && (( trim(strtoupper(Gc::$encoding)) == ConfigC::CHARACTER_UTF_8 ) || ( trim(strtolower(Gc::$encoding)) == ConfigC::CHARACTER_UTF8))) {
                if (UtilString::is_utf8($sqlstring)) {
                    $sqlstring = UtilString::utf82gbk($sqlstring);
                }
            }
            if (ConfigDb::$debug_show_sql) {
                LogMe::log("SQL: " . $sqlstring);
            }
            $this->stmt = odbc_exec($this->connection, $sqlstring);
            $parts = explode(" ", trim($sqlstring));
            $type  = strtolower($parts[0]);
            if (( CrudSqlUpdate::SQL_KEYWORD_UPDATE == $type ) || ( CrudSqlDelete::SQL_KEYWORD_DELETE == $type )) {
                return true;
            } elseif (CrudSqlInsert::SQL_KEYWORD_INSERT == $type) {
                $tablename = CrudSqlInsert::tablename($sqlstring);
                if (isset($tablename)) {
                    $object     = ConfigDb::tom($tablename);
                    $realIdName = DataObjectSpec::getRealIDColumnName($object);
                    $sql_maxid  = CrudSQL::SQL_MAXID;
                    $sql_maxid  = str_replace(CrudSQL::SQL_FLAG_ID, $realIdName, $sql_maxid);
                    $autoSql    = CrudSQL::SQL_SELECT . $sql_maxid . CrudSQL::SQL_FROM . $tablename;
                    if (ConfigDb::$debug_show_sql) {
                        LogMe::log("SQL: " . $autoSql);
                    }
                    $this->stmt = odbc_exec($this->connection, $autoSql);
                    $autoId     = odbc_result($this->stmt, 1);
                } else {
                    $autoId = -1;
                }
                return $autoId;
            }
            $result = $this->getResultToObjects($object);
            $sql_s  = preg_replace("/\s/", "", $sqlstring);
            $sql_s  = strtolower($sql_s);
            if (( !empty($result)) && !is_array($result)) {
                if (!( contains($sql_s, array("count(","sum(","max(","min(","sum(")))) {
                    $tmp      = $result;
                    $result   = null;
                    $result[] = $tmp;
                }
            }
        } catch (Exception $exc) {
            ExceptionDb::record($exc->getTraceAsString());
        }
        return $result;
    }

    /**
     * 对象总计数
     * @param string|class $object 需要查询的对象实体|类名称
     * @param object|string|array $filter 查询条件，在where后的条件
     * 示例如下:
     *
     *     0. "id=1,name='sky'"
     *     1. array("id=1","name='sky'")
     *     2. array("id"=>"1","name"=>"sky")
     *     3. 允许对象如new User(id="1",name="green");
     *
     * 默认:SQL Where条件子语句。如:(id=1 and name='sky') or (name like 'sky')
     *
     * @return int 对象总计数
     */
    public function count($object, $filter = null)
    {
        $result = null;
        try {
            if (!$this->validParameter($object)) {
                return 0;
            }
            $_SQL = new CrudSqlSelect();
            $_SQL->isPreparedStatement = true;
            $this->saParams            = $_SQL->parseValidInputParam($filter);
            $_SQL->isPreparedStatement = false;
            $this->sQuery              = $_SQL->select(CrudSqlSelect::SQL_COUNT)->from($this->classname)->where($this->saParams)->result();
            if (ConfigDb::$debug_show_sql) {
                LogMe::log("SQL: " . $this->sQuery);
                if (!empty($this->saParams)) {
                    LogMe::log("SQL PARAM: " . var_export($this->saParams, true));
                }
            }
            $this->stmt = odbc_exec($this->connection, $this->sQuery);
            $result     = odbc_result($this->stmt, 1);
            if (empty($result)) {
                return 0;
            }
            return $result;
        } catch (Exception $exc) {
            ExceptionDb::record($exc->getTraceAsString());
        }
    }

    /**
     * 对象分页
     *
     * @param string|class $object 需要查询的对象实体|类名称
     * @param int $startPoint  分页开始记录数
     * @param int $endPoint    分页结束记录数
     * @param object|string|array $filter 查询条件，在where后的条件
     * 示例如下:
     *
     *     0. "id=1,name='sky'"
     *     1. array("id=1","name='sky'")
     *     2. array("id"=>"1","name"=>"sky")
     *     3. 允许对象如new User(id="1",name="green");
     *
     * 默认:SQL Where条件子语句。如:(id=1 and name='sky') or (name like 'sky')
     *
     * @param string $sort 排序条件
     * 默认为 id desc
     *
     * 示例如下:
     *
     *     1.id asc;
     *     2.name desc;
     *
     * @return mixed 对象分页
     */
    public function queryPage($object, $startPoint, $endPoint, $filter = null, $sort = CrudSQL::SQL_ORDER_DEFAULT_ID)
    {
        try {
            if (($startPoint > $endPoint ) || ( $endPoint == 0)) {
                return null;
            }
            if (!$this->validParameter($object)) {
                return null;
            }

            $_SQL = new CrudSqlSelect();
            $_SQL->isPreparedStatement = true;
            $this->saParams            = $_SQL->parseValidInputParam($filter);
            $_SQL->isPreparedStatement = false;
            if ($sort == CrudSQL::SQL_ORDER_DEFAULT_ID) {
                $realIdName = $this->sql_id($object);
                $sort       = str_replace(CrudSQL::SQL_FLAG_ID, $realIdName, $sort);
            }
            $tablename    = ConfigOdbc::orm($this->classname);
            $whereclause  = SqlServerCrudSqlSelect::pageSql($startPoint, $endPoint, $_SQL, $tablename, $this->saParams, $sort);
            $this->sQuery = $_SQL->from($this->classname)->where($whereclause)->order($sort)->result();
            $result       = $this->sqlExecute($this->sQuery, $object);
            return $result;
        } catch (Exception $exc) {
            ExceptionDb::record($exc->getTraceAsString());
        }
    }
}
