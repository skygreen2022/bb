<?php

/**
 * -----------| 使用PHP5的Ms sql Extension:php_mssql |-----------
 *
 * 主要操作Microsoft的Sql Server 2000数据库
 *
 * 定义如下:
 *
 *     - 通用SQL的执行
 *     - 单个对象【单张表】的增删改查
 *     - 列表对象的查询
 *     - 统计函数的执行
 * @category Betterlife
 * @package core.db.object
 * @subpackage sqlserver
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
class DaoMssql extends Dao implements IDaoNormal
{
    public static $fetchmode = MSSQL_ASSOC;
    //MSSQL_ASSOC, MSSQL_NUM, and the default value of MSSQL_BOTH.

    /**
     * 连接数据库
     *
     * 说明:$dsn可以直接在System DSN里配置；然后在配置里设置:ConfigDb::$dbname
     * @param string $host
     * @param string $port
     * @param string $username
     * @param string $password
     * @param string $dbname
     * @return mixed 数据库连接
     */
    public function connect($host = null, $port = null, $username = null, $password = null, $dbname = null)
    {
        $connecturl = ConfigMssql::connctionurl($host, $port);

        if (!isset($username)) {
            $username = ConfigMssql::$username;
        }
        if (!isset($password)) {
            $password = ConfigMssql::$password;
        }
        if (!isset($dbname)) {
            $dbname   = ConfigMssql::$dbname;
        }
        if (ConfigOdbc::$is_persistent) {
            $this->connection = mssql_pconnect($connecturl, $username, $password);
        } else {
            $this->connection = mssql_connect($connecturl, $username, $password);
        }

        if (!$this->connection) {
            $errorinfo = "错误原因: " . mssql_get_last_message();
            ExceptionDb::log(Wl::ERROR_INFO_CONNECT_FAIL . $errorinfo);
        }
        mssql_select_db($dbname, $this->connection);
    }

    /**
     * 执行预编译SQL语句
     * @return void
     */
    private function executeSQL()
    {
        if (!empty($this->sQuery)) {
            if (ConfigDb::$debug_show_sql) {
                LogMe::log("SQL: " . $this->sQuery);
            }
            $this->result = mssql_query($this->sQuery);
            if (!$this->result) {
                $errorinfo = "错误原因:" . mssql_get_last_message();
                ExceptionDb::log(Wl::ERROR_INFO_DB_HANDLE . $errorinfo);
            }
        }
    }

    /**
     * 将查询结果转换成业务层所认知的对象
     * @param string $object 需要转换成的对象实体|类名称
     * @return mixed 转换成的对象实体列表
     */
    private function getResultToObjects($object)
    {
        $result = array();
        if (!mssql_num_rows($this->result)) {
            return null;
        }

        while ($row = mssql_fetch_array($this->result, self::$fetchmode)) {
            if (!empty($object)) {
                if ($this->validParameter($object)) {
                    $c        = UtilObject::array_to_object($row, $this->classname);
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
        $sql_s  = preg_replace("/\s/", "", $sqlstring);
        $sql_s  = strtolower($sql_s);
        if (!is_array($result)) {
            if (!( contain($sql_s, "count(") || contain($sql_s, "sum("))) {
                $tmp      = $result;
                $result   = null;
                $result[] = $tmp;
            }
        }
        mssql_free_result($this->result);
        return $result;
    }

    /**
     * 保存新建对象
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
                    ( ( trim(strtoupper(Gc::$encoding)) == ConfigC::CHARACTER_UTF_8 ) || ( trim(strtolower(Gc::$encoding)) == ConfigC::CHARACTER_UTF8) )
            ) {
                $this->saParams = UtilObject::object_to_array($object, false, array(ConfigC::CHARACTER_UTF_8 => ConfigC::CHARACTER_GBK));
            } else {
                $this->saParams = UtilObject::object_to_array($object);
            }
            $this->sQuery = $_SQL->insert($this->classname)->values($this->saParams)->result();
            $this->executeSQL();
            $this->sQuery = CrudSQL::SQL_SELECT . " @@IDENTITY as id";
            $this->executeSQL();
            if ($row = mssql_fetch_array($this->result, $this->fetchmode)) {
                $autoId = $row["id"];
            }
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
                $this->executeSQL();
                $result       = true;
            } catch (Exception $exc) {
                ExceptionDb::log($exc->getTraceAsString());
            }
        }
        return $result;
    }

    /**
     * 更新对象
     * @param int $id
     * @param Object $object
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
                $object->setId(null);
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
                $this->executeSQL();
                $result         = true;
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
     * 根据对象实体查询对象列表
     *
     * @param string $object 需要查询的对象实体|类名称
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
            $filter_arr                = $_SQL->parseValidInputParam($filter);
            $_SQL->isPreparedStatement = false;
            $this->sQuery              = $_SQL->select()->from($this->classname)->where($filter_arr)->order($sort)->limit($limit)->result();
            $this->executeSQL();
            $result                    = $this->getResultToObjects($object);
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
            $this->executeSQL();
            $result       = $this->getResultToObjects($object);
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
     * @param string|class $object 需要查询的对象实体|类名称
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
                $this->executeSQL();
                $result         = $this->getResultToObjects($object);
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
     * 直接执行SQL语句
     *
     * @param mixed $sql SQL查询|更新|删除语句
     * @param string|class $object 需要生成注入的对象实体|类名称
     * @return array
     * 返回
     *     - 1.执行查询语句返回对象数组
     *     - 2.执行更新和删除SQL语句返回执行成功与否的true|null
     * @return mixed
     */
    public function sqlExecute($sqlstring, $object = null)
    {
        $result = null;
        try {
            if (ConfigDb::$db == EnumDbSource::DB_SQLSERVER && (( trim(strtoupper(Gc::$encoding)) == ConfigC::CHARACTER_UTF_8 ) || ( trim(strtolower(Gc::$encoding)) == ConfigC::CHARACTER_UTF8) )) {
                if (UtilString::is_utf8($sqlstring)) {
                    $sqlstring = UtilString::utf82gbk($sqlstring);
                }
            }
            $this->sQuery = $sqlstring;
            $this->executeSQL();
            $parts = explode(" ", trim($sqlstring));
            $type  = strtolower($parts[0]);
            if (( CrudSqlUpdate::SQL_KEYWORD_UPDATE == $type ) || ( CrudSqlDelete::SQL_KEYWORD_DELETE == $type )) {
                return true;
            } elseif (CrudSqlInsert::SQL_KEYWORD_INSERT == $type) {
                $tablename = CrudSqlInsert::tablename($sqlstring);
                if (isset($tablename)) {
                    $object     = ConfigDb::tom($tablename);
                    $realIdName = DataObjectSpec::getRealIDColumnName(new $object());
                    $sql_maxid  = CrudSQL::SQL_MAXID;
                    $sql_maxid  = str_replace(CrudSQL::SQL_FLAG_ID, $realIdName, $sql_maxid);
                    $this->sQuery = CrudSQL::SQL_SELECT . $sql_maxid . CrudSQL::SQL_FROM . $tablename;
                    $this->executeSQL();
                    $row = mssql_fetch_array($this->result, $this->fetchmode);
                    if (isset($row) && array_key_exists($realIdName, $row)) {
                        $autoId = $row[$realIdName];
                        if (!empty($object) && is_object($object)) {
                            $object->setId($autoId);//当保存返回对象时使用
                        }
                    } else {
                        $autoId = -1;
                    }
                } else {
                    $autoId = -1;
                }
                return $autoId;
            }
            $result = $this->getResultToObjects($object);
            $sql_s  = preg_replace("/\s/", "", $sqlstring);
            $sql_s  = strtolower($sql_s);
            if (!empty($result) && !is_array($result)) {
                if (!( contains($sql_s, array("count(", "sum(", "max(", "min(", "sum(")))) {
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
            $this->executeSQL();
            $row = mssql_fetch_array($this->result, MSSQL_NUM);
            if ($row) {
                $result = $row[0];
            }
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
     *     1. id asc;
     *     2. name desc;
     *
     * @return mixed 对象分页
     */
    public function queryPage($object, $startPoint, $endPoint, $filter = null, $sort = CrudSQL::SQL_ORDER_DEFAULT_ID)
    {
        try {
            if (( $startPoint > $endPoint ) || ( $endPoint == 0)) {
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
            $tablename    = ConfigMssql::orm($this->classname);
            $whereclause  = SqlServerCrudSqlSelect::pageSql($startPoint, $endPoint, $_SQL, $tablename, $this->saParams, $sort);
            $this->sQuery = $_SQL->from($this->classname)->where($whereclause)->order($sort)->result();
            $result       = $this->sqlExecute($this->sQuery, $object);
            return $result;
        } catch (Exception $exc) {
            ExceptionDb::record($exc->getTraceAsString());
        }
    }
}
