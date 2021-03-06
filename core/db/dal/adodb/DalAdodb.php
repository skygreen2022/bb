<?php

/**
 * -----------| 优先实现数据库访问的Adodb DAL访问 |-----------
 *
 * ActiveRecord reference:object oriented programming with php5
 *
 * 当发生异常的时候，可在底层【library/adodb5】库通过print_r( sqlsrv_errors());查看错误原因
 *
 * @link http://phplens.com/lens/adodb/docs-adodb.htm#intro
 * @link http://adodb.sourceforge.net/
 * @link http://www.tsingfeng.com/?p=256
 * @category Betterlife
 * @package core.db.dal
 * @subpackage adodb
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
class DalAdodb extends Dal implements IDal
{
    /**
     * @var enum 当前使用的数据类型
     */
    private $dbtype;
    /**
     * 连接数据库
     * @global string $ADODB_FETCH_MODE
     * @param string $host
     * @param string $port
     * @param string $username
     * @param string $password
     * @param string $dbname
     * @param mixed $dbtype 指定数据库类型。{该字段的值参考: EnumDbSource}
     * @param mixed $engine 指定操作数据库引擎。{该字段的值参考: EnumDbEngine}
     * @return mixed 数据库连接
     */
    public function connect($host = null, $port = null, $username = null, $password = null, $dbname = null, $dbtype = null, $engine = null)
    {
        if (!isset($username)) {
            $username = ConfigAdodb::$username;
        }
        if (!isset($password)) {
            $password = ConfigAdodb::$password;
        }
        if (!isset($dbname)) {
            $dbname = ConfigAdodb::$dbname;
        }
        if (!isset($dbtype)) {
            $dbtype = ConfigAdodb::$db;
        }
        $this->dbtype = $dbtype;
        if (!isset($engine)) {
            $engine = ConfigAdodb::$engine;
        }

        try {
            switch ($dbtype) {
                case EnumDbSource::DB_SQLSERVER:
                case EnumDbSource::DB_MICROSOFT_ACCESS:
                case EnumDbSource::DB_DB2:
                    ConfigAdodb::$is_dsn_set = false;//当数据库为Sql Server,Microsoft Access,DB2时，Adodb只支持Dsn less
                    break;
            }
            global $ADODB_FETCH_MODE;
            $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
            if ($engine == EnumDbEngine::ENGINE_DAL_ADODB_PDO) {
                $this->connection = ADONewConnection("pdo");
            }
            if (ConfigAdodb::$is_dsn_set) {
                $conn_vars = array();
                if (ConfigAdodb::$is_persistent) {
                    $conn_vars[] = "persist";
                }
                if (!empty(ConfigAdodb::$dialect)) {
                    $conn_vars[] = "dialect=" . ConfigAdodb::$dialect;
                }
                $conn_str = "";
                if (count($conn_vars) > 0) {
                    $conn_str = "?" . implode("&", $conn_vars);
                }
                switch ($dbtype) {
                    case EnumDbSource::DB_MICROSOFT_ACCESS:
                    case EnumDbSource::DB_DB2:
                        if ($engine == EnumDbEngine::ENGINE_DAL_ADODB) {
                            $this->connection = ADONewConnection(ConfigAdodb::driver($dbtype));
                        }
                        $this->connection->Connect(ConfigAdodb::dsn($host, $port, $username, $password, $dbname, $dbtype, $engine));
                        break;
                    default:
                        if ($engine == EnumDbEngine::ENGINE_DAL_ADODB) {
                            $this->connection = ADONewConnection(ConfigAdodb::dsn($host, $port, $username, $password, $dbname, $dbtype, $engine) . $conn_str);
                        }
                        break;
                }
            } else {
                if ($engine == EnumDbEngine::ENGINE_DAL_ADODB) {
                    $this->connection = ADONewConnection(ConfigAdodb::driver($dbtype));
                }
                if ($dbtype == EnumDbSource::DB_ORACLE) {
                    $this->connection->connectSID = true;
                }
                switch ($dbtype) {
                    case EnumDbSource::DB_SQLSERVER:
                        $this->connection = ADONewConnection(ConfigAdodb::driver($dbtype));
                        if (( strtoupper(Gc::$encoding) == ConfigC::CHARACTER_UTF8 ) || ( strtoupper(Gc::$encoding) == ConfigC::CHARACTER_UTF_8 )) {
                            $this->connection->Connect(ConfigAdodb::dsn_less($host, $port, $username, $password, $dbname, $dbtype, $engine), $username, $password, $dbname);
                        } else {
                            $this->connection->Connect(ConfigAdodb::dsn_less($host, $port, $username, $password, $dbname, $dbtype, $engine), $username, $password);
                        }
                        break;
                    case EnumDbSource::DB_FIREBIRD:
                    case EnumDbSource::DB_INTERBASE:
                        if (ConfigDb::$is_persistent) {
                            $this->connection->PConnect(ConfigAdodb::dsn_less($host, $port, $username, $password, $dbname, $dbtype, $engine), $username, $password);
                        } else {
                            $this->connection->Connect(ConfigAdodb::dsn_less($host, $port, $username, $password, $dbname, $dbtype, $engine), $username, $password);
                        }
                        break;
                    case EnumDbSource::DB_SQLITE2:
                    case EnumDbSource::DB_SQLITE3:
                        if (ConfigDb::$is_persistent) {
                            $this->connection->PConnect(ConfigAdodb::dsn_less($host, $port, $username, $password, $dbname, $dbtype, $engine));
                        } else {
                            $this->connection->Connect(ConfigAdodb::dsn_less($host, $port, $username, $password, $dbname, $dbtype, $engine));
                        }
                        break;
                    default:
                        if (ConfigDb::$is_persistent) {
                            $this->connection->PConnect(ConfigAdodb::dsn_less($host, $port, $username, $password, $dbname, $dbtype, $engine), $username, $password, $dbname);
                        } else {
                            $this->connection->Connect(ConfigAdodb::dsn_less($host, $port, $username, $password, $dbname, $dbtype, $engine), $username, $password, $dbname);
                        }
                        break;
                }
            }

            if (!$this->connection) {
                ExceptionDb::log(Wl::ERROR_INFO_CONNECT_FAIL);
            }
        } catch (Exception $e) {
            ExceptionDb::log($e->getMessage());
        }
        $this->connection->debug = Gc::$dev_debug_on;
    }

    /**
     * 新建对象
     *
     * @link http://phplens.com/lens/adodb/docs-adodb.htm#intro
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
            $object->setCommitTime(UtilDateTime::now(EnumDateTimeFormat::TIMESTAMP));
            $this->saParams = UtilObject::object_to_array($object);
//            $sql = CrudSQL::SQL_SELECT . " * " . CrudSQL::SQL_FROM . $tablename . CrudSQL::SQL_WHERE . $this->sql_id($object) . self::EQUAL . "-1";
//            $rs  = $this->connection->Execute($sql); # Execute the query and get the empty recordset
//            $this->sQuery = $this->connection->GetInsertSQL($rs,  $this->saParams);

            //对SQL Server会报异常,故不使用:SQL error: [Microsoft][ODBC SQL Server Driver][SQL Server]列名 '********' 无效。, SQL state S0022 in SQLExecDirect
            $_SQL = new CrudSqlInsert();
            if (is_object($_SQL)) {
                $this->sQuery = $_SQL->insert($this->classname)->values($this->saParams)->result();
                if (ConfigDb::$db == EnumDbSource::DB_SQLSERVER && (( trim(strtoupper(Gc::$encoding)) == ConfigC::CHARACTER_UTF_8 ) || ( trim(strtolower(Gc::$encoding)) == ConfigC::CHARACTER_UTF8))) {
                    if (UtilString::is_utf8($this->sQuery) && ConfigAdodb::driver($this->dbtype) != ConfigAdodb::DRIVER_MSSQL_UTF8) {
                        $this->sQuery = UtilString::utf82gbk($this->sQuery);
                    }
                }
            }

            if (ConfigDb::$debug_show_sql) {
                LogMe::log("SQL: " . $this->sQuery);
                if (!empty($this->saParams)) {
                    LogMe::log("SQL PARAM:" . var_export($this->saParams, true));
                }
            }
            if ($this->connection->Execute($this->sQuery) === false) {
                ExceptionDb::log($this->connection->ErrorMsg());
            }
            $autoId = @$this->connection->Insert_ID();
            if (!$autoId) {
                $realIdName = DataObjectSpec::getRealIDColumnName($object);
                $sql_maxid  = CrudSQL::SQL_MAXID;
                $sql_maxid  = str_replace(CrudSQL::SQL_FLAG_ID, $realIdName, $sql_maxid);
                $tablename  = ConfigAdodb::orm($this->classname);
                $autoIdSql  = CrudSQL::SQL_SELECT . $sql_maxid . CrudSQL::SQL_FROM . $tablename;
                $this->stmt = $this->connection->Execute($autoIdSql);
                if (!empty($this->stmt) && (count($this->stmt->fields) > 0 )) {
                    $autoId = @$this->stmt->fields[0];
                }
            }
            //如果获取id报异常， 重置其为未获值
            if (!isset($autoId)) {
                $autoId = -1;
            }
        } catch (Exception $exc) {
            ExceptionDb::log($exc->getTraceAsString());
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
     * @return Object
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
                $_SQL  = new CrudSqlDelete();
                $where = $this->sql_id($object) . self::EQUAL . $id;
                $this->sQuery = $_SQL->deletefrom($this->classname)->where($where)->result();
                if (ConfigDb::$debug_show_sql) {
                    LogMe::log("SQL:" . $this->sQuery);
                }
                $this->connection->Execute($this->sQuery);
                $result = true;
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
     * @return Object
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
                $object->setUpdateTime(UtilDateTime::now(EnumDateTimeFormat::STRING));
                $this->saParams = UtilObject::object_to_array($object);
                unset($this->saParams[DataObjectSpec::getRealIDColumnName($object)]);
                $this->saParams = $this->filterViewProperties($this->saParams);
                $_SQL           = new CrudSqlUpdate();
                $_SQL->isPreparedStatement = false;
                $where          = $this->sql_id($object) . self::EQUAL . $id;
                $this->sQuery   = $_SQL->update($this->classname)->set($this->saParams)->where($where)->result();
                if (ConfigDb::$db == EnumDbSource::DB_SQLSERVER && (( trim(strtoupper(Gc::$encoding)) == ConfigC::CHARACTER_UTF_8 ) || ( trim(strtolower(Gc::$encoding)) == ConfigC::CHARACTER_UTF8))) {
                    if (UtilString::is_utf8($this->sQuery) && ConfigAdodb::driver($this->dbtype) != ConfigAdodb::DRIVER_MSSQL_UTF8) {
                        $this->sQuery = UtilString::utf82gbk($this->sQuery);
                    }
                }
                if (ConfigDb::$debug_show_sql) {
                    LogMe::log("SQL:" . $this->sQuery);
                    if (!empty($this->saParams)) {
                        LogMe::log("SQL PARAM:" . var_export($this->saParams, true));
                    }
                }
//                $tablename =ConfigAdodb::orm( $this->classname );
//                $sql = CrudSQL::SQL_SELECT . " * " . CrudSQL::SQL_FROM . $tablename.CrudSQL::SQL_WHERE . $this->sql_id($object) . self::EQUAL . $id;
//                $rs  = $this->connection->Execute($sql); # Execute the query and get the empty recordset
//                $this->sQuery = $this->connection->GetUpdateSQL($rs, $this->saParams);//对SQL Server会报异常,故不使用:SQL error:无法更新标识列
                $this->connection->Execute($this->sQuery);
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
     * @return array 转换成的对象实体列表
     */
    private function getResultToObjects($object)
    {
        if (empty($this->stmt)) {
            ExceptionDb::log($this->connection->ErrorMsg() . "");
        }
       // if ($this->stmt) {
       //    $numrows = $this->stmt->RecordCount();
       // } else {
       //    $numrows = 0;
       // }
        //if ($numrows >= 1) {
            $result = array();
        if (is_object($object)) {
            $classname = get_class($object);
        } elseif (!empty($object) && is_string($object)) {
            $classname = $object;
        }
            // 循环输出
        if ($this->stmt) {
            foreach ($this->stmt as $row) {//需要和GetAll配合使用
                //while ($row = $this->stmt->FetchNextObject()) { 需要和Execute配合使用
                if (empty($object)) {
                    if (count($row) == 1) {
                        foreach ($row as $key => $val) {
                            $rowObject = $val;
                        }
                    } else {
                        $rowObject = new stdClass();
                        foreach ($row as $key => $val) {
                            $rowObject->{$key} = $val;
                        }
                    }
                } else {
                    $rowObject = new $object();
                    if ($this->validParameter($object)) {
                        $rowObject = UtilObject::array_to_object($row, $this->classname);
                    }
                }
                $result[] = $rowObject;
            }
            $result = $this->getValueIfOneValue($result);
        }
        //}
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
     * 默认:SQL Where条件子语句。如: (id=1 and name='sky') or (name like 'sky')
     *
     * @param string $sort 排序条件
     * 默认为 id desc
     *
     * 示例如下:
     *
     *     1. id asc;
     *     2. name desc;
     *
     * @param string $limit 分页数目:同Mysql limit语法
     *
     * 示例如下:
     *
     *     - 0,10
     *
     * 列表:查询被列表的对象
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
                LogMe::log("SQL:" . $this->sQuery);
                if (!empty($this->saParams)) {
                    LogMe::log("SQL PARAM:" . var_export($this->saParams, true));
                }
            }
            $this->stmt = $this->connection->GetAll($this->sQuery);
            $result = $this->getResultToObjects($object);
            return $result;
        } catch (Exception $exc) {
            ExceptionDb::log($exc->getTraceAsString());
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
     * 默认:SQL Where条件子语句。如: (id=1 and name='sky') or (name like 'sky')
     *
     * @param string $sort 排序条件
     * 示例如下:
     *
     *     1.id asc;
     *     2.name desc;
     * @return 单个对象实体
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
            $this->sQuery   = $_SQL->select()->from($this->classname)->where($this->saParams)->order($sort)->result();

            if (ConfigDb::$debug_show_sql) {
                LogMe::log("SQL:" . $this->sQuery);
                if (!empty($this->saParams)) {
                    LogMe::log("SQL PARAM:" . var_export($this->saParams, true));
                }
            }
            $this->stmt = $this->connection->SelectLimit($this->sQuery, 1, 0);
            $result     = $this->getResultToObjects($object);
            if (count($result) > 0) {
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
     * @return 对象
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
                $this->stmt = $this->connection->GetAll($this->sQuery);
                $result     = $this->getResultToObjects($object);
                if (count($result) > 0) {
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
     * @param mixed $sql SQL查询|更新|删除语句
     * @param string|class $object 需要生成注入的对象实体|类名称
     * @return array
     * 返回
     *     - 执行查询语句返回对象数组
     *     - 执行更新和删除SQL语句返回执行成功与否的true|null
     */
    public function sqlExecute($sqlstring, $object = null)
    {
        $result = null;
        try {
            if (ConfigDb::$db == EnumDbSource::DB_SQLSERVER && (( trim(strtoupper(Gc::$encoding)) == ConfigC::CHARACTER_UTF_8 ) || ( trim(strtolower(Gc::$encoding)) == ConfigC::CHARACTER_UTF8))) {
                if (UtilString::is_utf8($sqlstring) && ConfigAdodb::driver($this->dbtype) != ConfigAdodb::DRIVER_MSSQL_UTF8) {
                    $sqlstring = UtilString::utf82gbk($sqlstring);
                }
            }
            if (ConfigDb::$debug_show_sql) {
                LogMe::log("SQL:" . $sqlstring);
            }
            $parts = explode(" ", trim($sqlstring));
            $type  = strtolower($parts[0]);
            if (( CrudSqlUpdate::SQL_KEYWORD_UPDATE == $type ) || ( CrudSqlDelete::SQL_KEYWORD_DELETE == $type )) {
                $this->stmt = $this->connection->Execute($sqlstring);
                return true;
            } elseif (CrudSqlInsert::SQL_KEYWORD_INSERT == $type) {
                $this->stmt = $this->connection->Execute($sqlstring);
                $autoId     = $this->connection->Insert_ID();
                if (!$autoId) {
                    $tablename = CrudSqlInsert::tablename($sqlstring);
                    if (isset($tablename)) {
                        $object     = ConfigDb::tom($tablename);
                        $realIdName = DataObjectSpec::getRealIDColumnName($object);
                        $sql_maxid  = CrudSQL::SQL_MAXID;
                        $sql_maxid  = str_replace(CrudSQL::SQL_FLAG_ID, $realIdName, $sql_maxid);

                        $autoIdSql  = CrudSQL::SQL_SELECT . $sql_maxid . CrudSQL::SQL_FROM . $tablename;
                        if (ConfigDb::$debug_show_sql) {
                            LogMe::log("SQL:" . $autoIdSql);
                        }
                        $this->stmt = $this->connection->Execute($autoIdSql);
                        if (( !empty($this->stmt) ) && (count($this->stmt->fields) > 0)) {
                            $autoId = @$this->stmt->fields[0];
                            if (!empty($object) && is_object($object)) {
                                $object->setId($autoId);//当保存返回对象时使用
                            }
                        } else {
                            $autoId = -1;
                        }
                    } else {
                        $autoId = -1;
                    }
                }
                return $autoId;
            }
            $this->stmt = $this->connection->GetAll($sqlstring);
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
     * 默认:SQL Where条件子语句。如: (id=1 and name='sky') or (name like 'sky')
     *
     * @return 对象总计数
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
            $this->sQuery = $_SQL->select(CrudSqlSelect::SQL_COUNT)->from($this->classname)->where($this->saParams)->result();
            if (ConfigDb::$debug_show_sql) {
                LogMe::log("SQL:" . $this->sQuery);
                if (!empty($this->saParams)) {
                    LogMe::log("SQL PARAM:" . var_export($this->saParams, true));
                }
            }
            $this->stmt = $this->connection->Execute($this->sQuery);
            if (!empty($this->stmt)) {
                $result = $this->stmt->fields[""];
            } else {
                $result = 0;
            }
            return $result;
        } catch (Exception $exc) {
            ExceptionDb::record($exc->getTraceAsString());
        }
    }

    /**
     * 对象分页
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
     * 默认:SQL Where条件子语句。如: (id=1 and name='sky') or (name like 'sky')
     *
     * @param string $sort 排序条件
     * 默认为 id desc
     *
     * 示例如下:
     *
     *      1.id asc;
     *      2.name desc;
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
            $this->sQuery = $_SQL->select()->from($this->classname)->where($this->saParams)->order($sort)->result();
            if (ConfigDb::$debug_show_sql) {
                LogMe::log("SQL:" . $this->sQuery);
                if (!empty($this->saParams)) {
                    LogMe::log("SQL PARAM:" . var_export($this->saParams, true));
                }
            }
            $this->stmt = $this->connection->SelectLimit($this->sQuery, ( $endPoint - $startPoint + 1 ), $startPoint - 1);
            //SelectLimit($sql,$numrows=-1,$offset=-1,$inputarr=false)
            //$offset从0开始
            $result = $this->getResultToObjects($object);
            return $result;
        } catch (Exception $exc) {
            ExceptionDb::record($exc->getTraceAsString());
        }
    }
}
