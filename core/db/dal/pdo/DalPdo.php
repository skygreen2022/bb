<?php

/**
 * -----------| 通过PDO调用数据库 |-----------
 *
 * @link http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html
 * @link http://www.leapsoul.cn/?p=651
 * @todo 实现PDO通用的DAL访问方式
 * @category Betterlife
 * @package core.db.dal
 * @subpackage pdo
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
class DalPdo extends Dal implements IDal
{
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
            $username = ConfigPdo::$username;
        }
        if (!isset($password)) {
            $password = ConfigPdo::$password;
        }
        if (!isset($dbtype)) {
            $dbtype   = ConfigPdo::$db;
        }
        if (!isset($engine)) {
            $engine   = ConfigAdodb::$engine;
        }

        try {
            if ($dbtype == EnumDbSource::DB_MICROSOFT_ACCESS) {
                $this->connection = new PDO(ConfigPdo::dsn($host, $port, $username, $password, $dbname, $dbtype, $engine));
            } else {
                $this->connection = new PDO(ConfigPdo::dsn($host, $port, $username, $password, $dbname, $dbtype, $engine), $username, $password);
            }
            if ($dbtype == EnumDbSource::DB_MYSQL) {
                $this->change_character_set($character_code = ConfigDb::$character);
            }
        } catch (PDOException $e) {
            ExceptionDb::log($e->getMessage());
        }
    }

    /**
     * 执行预编译SQL语句
     *
     * 可以防止SQL注入黑客技术
     */
    private function executeSQL()
    {
        try {
            if (ConfigDb::$debug_show_sql) {
                LogMe::log("SQL: " . $this->sQuery);
                if (!empty($this->saParams)) {
                    LogMe::log("SQL PARAM:" . var_export($this->saParams, true));
                }
            }
            $this->stmt  = $this->connection->prepare($this->sQuery);
            $columnCount = 0;
            if (!empty($this->saParams) && is_array($this->saParams) && (count($this->saParams) > 0 )) {
                foreach ($this->saParams as $key => $value) {
                    $columnCount += 1;
                    // echo $columnCount . $key . ":" . $value;
                    $saveColumn[$columnCount] = $value;
                    $this->stmt->bindParam($columnCount, $saveColumn[$columnCount]);
                }
            }
            $this->stmt->execute();
        } catch (Exception $exc) {
            ExceptionDb::log($exc->getTraceAsString());
        }
    }

    /**
     * 将查询结果转换成业务层所认知的对象
     * @param string $object 需要转换成的对象实体|类名称
     * @return 转换成的对象实体列表
     */
    private function getResultToObjects($object)
    {
        $result = null;
        $rows   = $this->stmt->fetchAll(ConfigPdo::$fetchmode);
        foreach ($rows as $row) {
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
     *  直接执行SQL语句
     *
     * @param mixed $sql SQL查询语句
     * @param string|class $object 需要生成注入的对象实体|类名称
     * @return mixed 返回数据
     */
    public function sqlExecute($sql, $object = null)
    {
        $result = null;
        try {
            if (ConfigDb::$debug_show_sql) {
                LogMe::log("SQL: " . $sql);
            }
            $this->stmt = $this->connection->prepare($sql);
            $this->stmt->execute();
            $parts = explode(" ", trim($sql));
            $type  = strtolower($parts[0]);
            if (( CrudSqlUpdate::SQL_KEYWORD_UPDATE == $type ) || ( CrudSqlDelete::SQL_KEYWORD_DELETE == $type )) {
                return true;
            } elseif (CrudSqlInsert::SQL_KEYWORD_INSERT == $type) {
                $autoId = $this->connection->lastInsertId();
                return $autoId;
            }
            $result = $this->getResultToObjects($object);
            $sql_s  = preg_replace("/\s/", "", $sql);
            $sql_s  = strtolower($sql_s);
            if (( !empty($result) ) && (!is_array($result) )) {
                if (!( contains($sql_s, array("count(", "sum(", "max(", "min(", "sum(")))) {
                    $tmp      = $result;
                    $result   = null;
                    $result[] = $tmp;
                }
            }
            return $result;
        } catch (Exception $exc) {
            ExceptionDb::log($exc->getTraceAsString());
        }
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
            $this->saParams = $_SQL->parseValidInputParam($filter);
            $_SQL->isPreparedStatement = false;
            $this->sQuery   = $_SQL->select(CrudSqlSelect::SQL_COUNT)->from($this->classname)->where($this->saParams)->result();
            if (ConfigDb::$debug_show_sql) {
                LogMe::log("SQL: " . $this->sQuery);
                if (!empty($this->saParams)) {
                    LogMe::log("SQL PARAM: " . var_export($this->saParams, true));
                }
            }
            $this->stmt = $this->connection->query($this->sQuery, PDO::FETCH_NUM);
            $result     = $this->stmt->fetchColumn();
            return $result;
        } catch (Exception $exc) {
            ExceptionDb::log($exc->getTraceAsString());
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
            if (ConfigDb::$db == EnumDbSource::DB_MYSQL) {
                $this->sQuery = $_SQL->select()->from($this->classname)->where($this->saParams)->order($sort)->limit($startPoint . "," . ($endPoint - $startPoint + 1))->result();
            } elseif (ConfigDb::$db == EnumDbSource::DB_MICROSOFT_ACCESS) {
                $tablename    = ConfigPdo::orm($this->classname);
                $whereclause  = SqlServerCrudSqlSelect::pageSql($startPoint, $endPoint, $_SQL, $tablename, $this->saParams, $sort);
                $this->sQuery = $_SQL->select()->from($this->classname)->where($whereclause)->order($sort)->result();
            } else {
                $this->sQuery = $_SQL->select()->from($this->classname)->where($this->saParams)->order($sort)->limit($startPoint . "," . ($endPoint - $startPoint + 1))->result();
            }
            $result = $this->sqlExecute($this->sQuery, $object);
            return $result;
        } catch (Exception $exc) {
            ExceptionDb::log($exc->getTraceAsString());
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
            $_SQL->isPreparedStatement = true;
            $object->setCommitTime(UtilDateTime::now(EnumDateTimeFormat::TIMESTAMP));
            $this->saParams = UtilObject::object_to_array($object);
            $this->sQuery   = $_SQL->insert($this->classname)->values($this->saParams)->result();
            $this->executeSQL();
            $autoId         = $this->connection->lastInsertId();
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
                $object->setUpdateTime(UtilDateTime::now(EnumDateTimeFormat::STRING));
                $this->saParams = UtilObject::object_to_array($object);
                unset($this->saParams[DataObjectSpec::getRealIDColumnName($object)]);
                $this->saParams = $this->filterViewProperties($this->saParams);
                $where          = $this->sql_id($object) . self::EQUAL . $id;
                $this->sQuery   = $_SQL->update($this->classname)->set($this->saParams)->where($where)->result();
                $this->executeSQL();
                $result         = true;
            } catch (Exception $exc) {
                ExceptionDb::log($exc->getTraceAsString());
                $result         = false;
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
     * @param string $filter 查询条件，在where后的条件
     * 示例如下
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
     * 默认为 id desc
     *
     *     1. id asc;
     *     2. name desc;
     * @param string $limit 分页数目: 同Mysql limit语法
     * 示例如下:
     *
     *    0,10
     *
     * @return mixed 对象列表数组
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
            if (is_array($filter_arr) && count($filter_arr) > 0) {
                $this->saParams = $filter_arr;
            } else {
                $_SQL->isPreparedStatement = false;
            }
            if (ConfigDb::$db == EnumDbSource::DB_MYSQL) {
                $this->sQuery = $_SQL->select()->from($this->classname)->where($filter_arr)->order($sort)->limit($limit)->result();
            } elseif (ConfigDb::$db == EnumDbSource::DB_MICROSOFT_ACCESS) {
                $tablename    = ConfigPdo::orm($this->classname);
                $whereclause  = SqlServerCrudSqlSelect::getSql($_SQL, $tablename, $filter_arr, $sort, $limit);
                $this->sQuery = $_SQL->select()->from($this->classname)->where($whereclause)->order($sort)->result();
            } else {
                $this->sQuery = $_SQL->select()->from($this->classname)->where($filter_arr)->order($sort)->limit($limit)->result();
            }
            $this->executeSQL();
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
            $filter_arr = $_SQL->parseValidInputParam($filter);
            if (is_array($filter_arr) && count($filter_arr) > 0) {
                $this->saParams = $filter_arr;
            } else {
                $_SQL->isPreparedStatement = false;
            }
            if ($sort == CrudSQL::SQL_ORDER_DEFAULT_ID) {
                $realIdName = $this->sql_id($object);
                $sort       = str_replace(CrudSQL::SQL_FLAG_ID, $realIdName, $sort);
            }
            $this->sQuery = $_SQL->select()->from($this->classname)->where($filter_arr)->order($sort)->result();
            $this->executeSQL();
            $rows = $this->stmt->fetchAll();
            if (count($rows) > 0) {
                $result = UtilObject::array_to_object($rows[0], $this->classname);
            }
            return $result;
        } catch (Exception $exc) {
            ExceptionDb::log($exc->getTraceAsString());
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

            if ($id != null && $id > 0) {
                $_SQL         = new CrudSqlSelect();
                $where        = $this->sql_id($object) . self::EQUAL . $id;
                $this->sQuery = $_SQL->select()->from($this->classname)->where($where)->result();
                if (ConfigDb::$debug_show_sql) {
                    LogMe::log("SQL:" . $this->sQuery);
                }
                $rows = $this->connection->query($this->sQuery);
                $rows->setFetchMode(PDO::FETCH_OBJ);
                foreach ($rows as $row) {
                    $result = UtilObject::array_to_object($row, $this->classname);
                }
            }
            return $result;
        } catch (Exception $exc) {
            ExceptionDb::log($exc->getTraceAsString());
        }
    }
}
