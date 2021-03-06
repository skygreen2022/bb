<?php

// phpcs:disable
DataObjectSpec::init();
// phpcs:enable
/**
 * -----------| 所有数据实体类如POJO的父类 |-----------
 *
 * 该实体类设计为ActiveRecord模式。
 *
 * 可直接在对象上操作CRUD增删改查操作
 *
 * 查主要为: 根据主键和名称查找对象。
 *
 *          总记录数和分页查找等常规方法。
 *
 * 框架定义数据对象的默认列[关键字可通过数据对象列规格$field_spec修改]:
 *
 * - id        : 数据对象的唯一标识
 *
 * - committime: 数据创建的时间, 当没有updateTime时, 其亦代表数据最后更新的时间
 *
 * - updateTime: 数据最后更新的时间。
 * @category Betterlife
 * @package core.model
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
abstract class DataObject extends BBObject implements ArrayAccess
{
    //<editor-fold defaultstate="collapsed" desc="定义部分">
    /**
     * @var enum $idname_strategy ID名称定义的策略
     */
    public static $idname_strategy = EnumIDNameStrategy::TABLENAME_ID;
    /**
     * ID名称中的连接符。
     *
     * ID名称定义的策略为TABLENAME_ID有效。
     * @static
     */
    public static $idname_concat = '_';
    /**
     * Foreign ID名称定义的策略
     * @var enum $foreignid_name_strategy
     */
    public static $foreignid_name_strategy = EnumForeignIDNameStrategy::TABLENAME_ID;
    /**
     * Foreign ID名称中的连接符。
     * Foreign ID名称定义的策略为TABLENAME_ID有效。
     * @static
     */
    public static $foreignid_concat = '_';
    /**
     * 数据对象定义需定义字段: public $field_spec
     *
     * 它定义了当前数据对象的列规格说明。
     *
     * 数据对象的列规格说明可参考DataObjectSpec::$field_spec_default的定义
     */
    public $field_spec;
    /**
     * @var mixed 数据对象的唯一标识
     */
    protected $id;
    /**
     * @var int 记录创建的时间timestamp
     */
    public $commitTime;
    /**
     * @var int 记录最后更新的时间, 当表中无该字段时, 一般用commitTime记录最后更新的时间。
     */
    public $updateTime;
    /**
     * @var IDao 当前使用的数据库调用对象
     */
    private static $currentDao;
    /**
     * 获取当前使用的数据库调用对象
     * @return IDao
     */
    public static function dao()
    {
        if (!isset(self::$currentDao)) {
            self::$currentDao = ManagerDb::newInstance()->dao();
        }
        return self::$currentDao;
    }
    /**
     * 静态方法:获取数据对象的类名
     */
    public static function cnames()
    {
        return self::classname_static();
    }
    /**
     * 静态方法:获取数据对象的类名
     */
    public static function classname_static()
    {
        $result = get_called_class();
        return $result;
    }
    //</editor-fold>

    //<editor-fold defaultstate="collapsed" desc="魔术方法">
    /**
     * 从数组创建对象。
     * @param mixed $array
     * @return DataObject
     */
    public function __construct($array = null)
    {
        if (!empty($array)) {
            $id_name = DataObjectSpec::getRealIDColumnNameStatic($this);
            if (is_array($array) && array_key_exists($id_name, $array)) {
                if (empty($array[$id_name])) {
                    unset($array[$id_name]);
                }
            }
            UtilObject::array_to_object($array, $this);
        }
    }

    /**
     * 说明: 若每个具体的实现类希望不想实现set,get方法；
     *
     *      则将该方法复制到每个具体继承他的对象类内。
     *
     * - 可设定对象未定义的成员变量[但不建议这样做]
     * - 可无需定义get方法和set方法
     * - 类定义变量访问权限设定需要是pulbic
     * @param string $method 方法名
     * @param array $arguments 传递的变量数组
     */
    public function __call($method, $arguments)
    {
        return DataObjectFunc::call($this, $method, $arguments);
    }

    /**
     * 可设定对象未定义的成员变量[但不建议这样做]
     *
     * 类定义变量访问权限设定需要是pulbic
     * @param mixed $property 属性名
     * @return mixed 属性值
     */
    public function __get($property)
    {
        return DataObjectFunc::get($this, $property);
    }

    /**
     * 可设定对象未定义的成员变量[但不建议这样做]
     *
     * 类定义变量访问权限设定需要是pulbic
     * @param mixed $property 属性名
     * @param mixed $value 属性值
     */
    public function __set($property, $value)
    {
        return DataObjectFunc::set($this, $property, $value);
    }

    /**
     * 打印当前对象的数据结构
     * @return string 描述当前对象。
     */
    public function __toString()
    {
        return DataObjectFunc::toString($this);
    }
    //</editor-fold>

    /**
     * 处理表之间一对一, 一对多, 多对多的关系
     */
    public function getMutualRelation($property)
    {
        return DataObjectRelation::getMutualRelation($this, $property);
    }

    //<editor-fold defaultstate="collapsed" desc="默认列Setter和Getter">
    /**
     * @var array 存放当前数据对象的列规格说明
     */
    public $real_fieldspec;

    /**
     * 设置唯一标识
     * @param mixed $id
     */
    public function setId($id)
    {
        if (DataObjectSpec::isNeedID($this)) {
            $columnName = DataObjectSpec::getRealIDColumnName($this);
            $this->$columnName = $id;
        }
        unset($this->real_fieldspec);
    }

    /**
     * 获取唯一标识
     * @return mixed
     */
    public function getId()
    {
        if (DataObjectSpec::isNeedID($this)) {
            $columnName = DataObjectSpec::getRealIDColumnName($this);
            unset($this->real_fieldspec);
            return $this->$columnName;
        } else {
            unset($this->real_fieldspec);
            return null;
        }
    }

    /**
     * 设置数据创建的时间
     * @param mixed $commitTime
     */
    public function setCommitTime($commitTime)
    {
        if (DataObjectSpec::isNeedCommitTime($this)) {
            $columnName = DataObjectSpec::getRealColumnName($this, EnumColumnNameDefault::COMMITTIME);
            $this->$columnName = $commitTime;
        }
        unset($this->real_fieldspec);
    }

    /**
     * 获取数据创建的时间
     * @return mixed
     */
    public function getCommitTime()
    {
        if (DataObjectSpec::isNeedCommitTime($this)) {
            $columnName = DataObjectSpec::getRealColumnName($this, EnumColumnNameDefault::COMMITTIME);
            unset($this->real_fieldspec);
            return $this->$columnName;
        } else {
            unset($this->real_fieldspec);
            return null;
        }
        //return $this->commitTime;
    }

    /**
     * 设置数据最后更新的时间
     * @param mixed $updateTime
     */
    public function setUpdateTime($updateTime)
    {
        if (DataObjectSpec::isNeedUpdateTime($this)) {
            $columnName = DataObjectSpec::getRealColumnName($this, EnumColumnNameDefault::UPDATETIME);
            $this->$columnName = $updateTime;
        }
        // else{$this->setCommitTime($updateTime);}
        unset($this->real_fieldspec);
    }

    /**
     * 获取数据最后更新的时间
     * @return mixed
     */
    public function getUpdateTime()
    {
        if (DataObjectSpec::isNeedUpdateTime($this)) {
            $columnName = DataObjectSpec::getRealColumnName($this, EnumColumnNameDefault::UPDATETIME);
            unset($this->real_fieldspec);
            return $this->$columnName;
        } else {
            unset($this->real_fieldspec);
            return $this->getCommitTime();
        }
        //return $this->updateTime;
    }
    //</editor-fold>

    //<editor-fold defaultstate="collapsed" desc="定义数组进入对象方式">
    /**
     * Offset to retrieve
     *
     * @param mixed $key — The offset to retrieve.
     * @access public
     * @return mixed — Can return all value types.
     * @abstracting ArrayAccess
     */
    public function offsetGet($key)
    {
        $method = "get" . ucfirst($key);
        return $this->$method();
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $key — An offset to check for.
     * @access public
     * @return bool
     * @abstracting ArrayAccess
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        $method = "get" . ucfirst($key);
        return method_exists($this, $method);
    }

    /**
     * Offset to set
     * @param mixed $key — The offset to assign the value to.
     * @param mixed $value — The value to set.
     * @access public
     * @return void
     * @abstracting ArrayAccess
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        $method = "set" . ucfirst($key);
        $this->$method($value);
        //$this->$key = $value;
    }

    /**
     * Offset to unset
     * @param mixed $key — The offset to unset.
     * @access public
     * @return void
     * @abstracting ArrayAccess
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        unset($this->$key);
    }
    //</editor-fold>

    //<editor-fold defaultstate="collapsed" desc="数据持久化: 数据库的CRUD操作">
    /**
     * 获取当前数据对象的表名
     */
    public static function tablename()
    {
        return ConfigDb::orm(get_called_class());
    }

    /**
     * 根据数据对象的属性名获取属性名的显示。
     * @param mixed $data 数据对象数组。如:array(user,user)
     * @param mixed $property_name  属性名【可以一次指定多个属性名】
     */
    public static function propertyShow($data, $property_name)
    {
        DataObjectFunc::propertyShow($data, get_called_class(), $property_name);
    }

    /**
     * 保存前操作
     */
    protected function onBeforeWrite()
    {
    }

    /**
     * 保存当前对象
     * @return boolen 是否新建成功；true为操作正常
     */
    protected function write()
    {
        $this->save();
    }

    /**
     * 保存当前对象
     * @example 示例如下
     * 示例如下:
     *
     *       $user   = new User();
     *
     *       $user->setUsername( "betterlife" );
     *
     *       $user->setPassword( "123456" );
     *
     *       $user_id = $user->save();
     * @return int 保存对象记录的ID标识号
     */
    public function save()
    {
        $this->onBeforeWrite();
        $id = $this->getId();
        if (empty($id)) {
            $idColumn = DataObjectSpec::getRealIDColumnName($this);
            unset($this->{$idColumn});
        }
        return self::dao()->save($this);
    }

    /**
     * 数据对象存在多对多|从属于多对多关系时, 因为存在一张中间表。
     *
     * 因此它们的关系需要单独进行存储
     *
     * @example 示例如下:
     *
     * 示例1【多对多-主控端】:
     *
     *      $user = new User();
     *
     *      $user->setId(2);
     *
     *      $user->saveRelationForManyToMany( "roles", "3", array("commitTime" => date("Y-m-d H:i:s")) );
     *
     *      说明:roles是在User数据对象中定义的变量:
     *
     *      static $many_many = array(
     *
     *          "roles" => "Role",
     *
     *      );
     *
     * 示例2【多对多-被控端】:
     *
     *      $role = new Role();
     *
     *      $role->setId(5);
     *
     *      $role->saveRelationForManyToMany( "users", "6", array("commitTime" => date("Y-m-d H:i:s")) );
     *
     *      说明:users是在Role数据对象中定义的变量:
     *
     *      static $belongs_many_many = array(
     *
     *          "users" => "User",
     *
     *      );
     *
     * @param mixed $relation_object 多对多|从属于多对多关系定义对象
     * @param mixed $relation_id_value 关系对象的主键ID值。
     * @param array $other_column_values 其他列值键值对【冗余字段便于查询的数据列值】, 如有一列: 记录关系创建时间。
     * @return mixed 保存对象后的主键
     */
    public function saveRelationForManyToMany($relation_object, $relation_id_value, $other_column_values = null)
    {
        return DataObjectRelation::saveRelationForManyToMany($this, $relation_object, $relation_id_value, $other_column_values);
    }

    /**
     * 同步删除取消了已有多对多关系、保存新增多对多关系
     *
     * 能够比对数据库数据，以$other_ids为准，如果相同的行就不删除，如果不存在的删除，新的就增加
     * @example 示例如下:
     *
     *     Userrole::saveDeleteRelateions( "user_id", 1, "role_id", array(1, 2) );
     * @param string $id_name 主标识名称
     * @param int $id 主标识
     * @param string $rel_name 关系标识名称
     * @param array $other_ids 关系标识组
     * @return void
     */
    public static function saveDeleteRelateions($id_name, $id, $rel_name, $other_ids)
    {
        return DataObjectRelation::saveDeleteRelateions(get_called_class(), $id_name, $id, $rel_name, $other_ids);
    }

    /**
     * 由标识删除指定ID数据对象
     * @example 示例如下
     *
     *       $isDelete = User::deleteByID( 4 );
     * @param mixed $id 数据对象编号
     * @return boolen 是否修改成功
     */
    public static function deleteByID($id)
    {
        return DataObjectFunc::deleteByID(get_called_class(), $id);
    }

    /**
     * 根据主键删除多条记录
     * @example 示例如下
     *
     *       $isDelete = User::deleteByIds( "5, 6, 7" );
     * @param array|string $ids 数据对象编号
     *
     * 形式如下:
     *     1. array: array(1, 2, 3, 4, 5)
     *     2. 字符串: 1, 2, 3, 4
     * @return boolen 是否修改成功
     */
    public static function deleteByIds($ids)
    {
        return DataObjectFunc::deleteByIds(get_called_class(), $ids);
    }

    /**
     * 根据条件删除多条记录
     * @example 示例如下
     *
     *       $isDelete = User::deleteBy( "username = 'betterlife7'" );
     * @param mixed $filter 查询条件, 在where后的条件
     * @example 示例如下:
     * 示例如下:
     *
     *        0. "id = 1, name = 'sky'"
     *        1. array("id = 1", "name = 'sky'")
     *        2. array("id" => "1", "name" => "sky")
     *        3. 允许对象如new User(id = "1", name = "green");
     *
     * 默认:SQL Where条件子语句。如: "( id = 1 and name = 'sky' ) or ( name like '%sky%' )"
     * @return boolen 是否修改成功
     */
    public static function deleteBy($filter)
    {
        return DataObjectFunc::deleteBy(get_called_class(), $filter);
    }

    /**
     * 删除当前对象
     * @example 示例如下
     *
     *       $user     = User::getById( 3 );
     *
     *       $isDelete = $user->delete();
     * @return boolen 是否删除成功；true为操作正常
     */
    public function delete()
    {
        return self::dao()->delete($this);
    }

    /**
     * 保存或更新当前对象
     * @example 示例如下
     * 示例如下:
     *
     *       $user             = User::getById( 3 );
     *
     *       $user["username"] = "shanghai";
     *
     *       $user_id          = $user->saveOrUpdate();
     * @return boolen 是否保存或更新成功；true为操作正常
     */
    public function saveOrUpdate()
    {
        return self::dao()->saveOrUpdate($this);
    }

    /**
     * 更新当前对象
     * @example 示例如下
     * 示例如下:
     *
     *       $user             = User::getById( 3 );
     *
     *       $user["username"] = "shanghai";
     *
     *       $user->update();
     * @return boolen 是否更新成功；true为操作正常
     */
    public function update()
    {
        $result = self::dao()->update($this);
        unset($this["real_fieldspec"]);
        return $result;
    }

    /**
     * 更新对象指定的属性
     * @example 示例如下
     *
     *        $isUpdate = User::updateProperties( "1, 2", "loginTimes = 100" );
     * @param array|string $sql_ids 需更新数据的ID编号或者ID编号的Sql语句
     * 示例如下:
     *
     *        1. 1, 2, 3
     *        2. array(1, 2, 3)
     *
     * @param string $array_properties 指定的属性
     * 示例如下:
     *
     *        1. pass = 1, name = 'sky'
     *        2. array("pass" => "1", "name" => "sky")
     * @return boolen 是否更新成功；true为操作正常
     */
    public static function updateProperties($sql_ids, $array_properties)
    {
        return DataObjectFunc::updateProperties(get_called_class(), $sql_ids, $array_properties);
    }

    /**
     * 根据条件更新数据对象指定的属性
     * @example 示例如下
     *
     *        $isUpdate = User::updateBy( "username = 'admin'", "loginTimes = 500" );
     * @param mixed $filter 查询条件, 在where后的条件
     * 示例如下:
     *
     *        0. "id = 1, name = 'sky'"
     *        1. array("id = 1", "name = 'sky'")
     *        2. array("id" => "1", "name" => "sky")
     *        3. 允许对象如new User(id = "1", name = "green");
     *
     * 默认:SQL Where条件子语句。如: "( id = 1 and name = 'sky' ) or ( name like '%sky%' )"
     *
     * @param string $array_properties 指定的属性
     * 示例如下:
     *
     *        1. pass = 1, name = 'sky'
     *        2. array("pass" => "1", "name" => "sky")
     * @return boolen 是否更新成功；true为操作正常
     */
    public static function updateBy($filter, $array_properties)
    {
        return DataObjectFunc::updateBy(get_called_class(), $filter, $array_properties);
    }

    /**
     * 对属性进行递增
     * @example 示例如下
     *
     *        $isPlus = User::increment( "loginTimes", 5, "user_id > 1" );
     * @param object|string|array $filter 查询条件, 在where后的条件
     * 示例如下:
     *
     *        0. "id = 1, name = 'sky'"
     *        1. array("id = 1", "name = 'sky'")
     *        2. array("id" => "1", "name" => "sky")
     *        3. 允许对象如new User(id = "1", name = "green");
     *
     * 默认:SQL Where条件子语句。如: "( id = 1 and name = 'sky' ) or ( name like '%sky%' )"
     * @param string property_name 属性名称
     * @param int incre_value 递增数
     * @return boolen 是否修改成功
     */
    public static function increment($property_name, $incre_value = 1, $filter = null)
    {
        return DataObjectFunc::increment(get_called_class(), $property_name, $incre_value, $filter);
    }

    /**
     * 对属性进行递减
     * @example 示例如下
     *
     *        $isMinus = User::decrement( "loginTimes", 3, "user_id > 1" );
     * @param object|string|array $filter 查询条件, 在where后的条件
     * 示例如下:
     *
     *        0. "id = 1, name = 'sky'"
     *        1. array("id = 1", "name = 'sky'")
     *        2. array("id" => "1", "name" => "sky")
     *        3. 允许对象如new User(id = "1", name = "green");
     *
     * 默认:SQL Where条件子语句。如: "( id = 1 and name = 'sky' ) or ( name like '%sky%' )"
     * @param string property_name 属性名称
     * @param int decre_value 递减数
     * @return boolen 是否修改成功
     */
    public static function decrement($property_name, $decre_value = 1, $filter = null)
    {
        return DataObjectFunc::decrement(get_called_class(), $property_name, $decre_value, $filter);
    }

    /**
     * 由标识判断指定ID数据对象是否存在
     * @example 示例如下
     *
     *        $isExist = User::existByID( 1 );
     * @param mixed $id 数据对象编号
     * @return bool 是否存在
     */
    public static function existByID($id)
    {
        return DataObjectFunc::existByID(get_called_class(), $id);
    }

    /**
     * 判断符合条件的数据对象是否存在
     * @example 示例如下
     *
     *        $isExist = User::existBy( "username = 'china'" );
     * @param mixed $filter 查询条件, 在where后的条件
     * 示例如下:
     *
     *        0. "id = 1, name = 'sky'"
     *        1. array("id = 1", "name = 'sky'")
     *        2. array("id" => "1", "name" => "sky")
     *        3. 允许对象如new User(id = "1", name = "green");
     *
     * 默认:SQL Where条件子语句。如: "( id = 1 and name = 'sky' ) or ( name like '%sky%' )"
     * @return bool 是否存在
     */
    public static function existBy($filter)
    {
        return DataObjectFunc::existBy(get_called_class(), $filter);
    }

    /**
     * 查询当前对象需显示属性的列表
     * @example 示例如下
     *
     *        $blog_names = Blog::select( "blog_name" );
     * @param string $columns指定的显示属性, 同SQL语句中的Select部分。
     * 示例如下:
     *
     *        id,name,commitTime
     *
     * @param object|string|array $filter 查询条件, 在where后的条件
     * 示例如下:
     *
     *        0. "id = 1, name = 'sky'"
     *        1. array("id = 1", "name = 'sky'")
     *        2. array("id" => "1", "name" => "sky")
     *        3. 允许对象如new User(id = "1", name = "green");
     *
     * 默认:SQL Where条件子语句。如: "( id = 1 and name = 'sky' ) or ( name like '%sky%' )"
     *
     * @param string $sort 排序条件
     * 示例如下:
     *
     *        1. id asc;
     *        2. name desc;
     *
     * @param string $limit 分页数量:limit起始数被改写, 默认从1开始, 如果是0, 同Mysql limit语法；
     * 示例如下:
     *
     *    6, 10  从第6条开始取10条(如果是mysql的limit, 意味着从第五条开始, 框架里不是这个意义。)
     *    1, 10 (相当于第1-第10条)
     *    10 (相当于第1-第10条)
     *
     * @return 查询列数组, 当只有一个值的时候如select count(表名_id), 自动从数组中转换出来值字符串
     */
    public static function select($columns, $filter = null, $sort = CrudSQL::SQL_ORDER_DEFAULT_ID, $limit = null)
    {
        return DataObjectFunc::showColumns(get_called_class(), $columns, $filter, $sort, $limit);
    }

    /**
     * 查询当前对象单个需显示的属性
     * @example 示例如下
     *
     *        $blog_name = Blog::selectOne( "blog_name" );
     * @param string 指定的显示属性, 同SQL语句中的Select部分。
     * 示例如下:
     *
     *        id, name, commitTime
     *
     * @param object|string|array $filter 查询条件, 在where后的条件
     * 示例如下:
     *
     *        0. "id = 1, name = 'sky'"
     *        1. array("id = 1", "name = 'sky'")
     *        2. array("id" => "1", "name" => "sky")
     *        3. 允许对象如new User(id = "1", name = "green");
     *
     * 默认:SQL Where条件子语句。如: "( id = 1 and name = 'sky' ) or ( name like '%sky%' )"
     *
     * @param string $sort 排序条件
     * 示例如下:
     *        1. id asc;
     *        2. name desc;
     * @return 查询列数组, 自动从数组中转换出来值字符串,最后只返回一个值
     */
    public static function selectOne($columns, $filter = null, $sort = CrudSQL::SQL_ORDER_DEFAULT_ID)
    {
        $result = DataObjectFunc::showColumns(get_called_class(), $columns, $filter, $sort, "0,1");
        if (!empty($result) && (is_array($result) ) && (count($result) > 0 )) {
            $result = $result[0];
        }
        return $result;
    }

    /**
     * 查询数据对象列表
     * @example 示例如下
     *
     *        $blogs = Blog::get();
     * @param object|string|array $filter 查询条件, 在where后的条件
     * 示例如下:
     *
     *        0. "id = 1, name = 'sky'"
     *        1. array("id = 1", "name = 'sky'")
     *        2. array("id" => "1", "name" => "sky")
     *        3. 允许对象如new User(id = "1", name = "green");
     *
     * 默认:SQL Where条件子语句。如: "( id = 1 and name = 'sky' ) or ( name like '%sky%' )"
     *
     * @param string $sort 排序条件
     * 示例如下:
     *
     *        1. id asc;
     *        2. name desc;
     *
     * @param string $limit 分页数量:limit起始数被改写, 默认从1开始, 如果是0, 同Mysql limit语法；
     * 示例如下:
     *
     *    6, 10  从第6条开始取10条(如果是mysql的limit, 意味着从第五条开始, 框架里不是这个意义。)
     *    1, 10 (相当于第1-第10条)
     *    10 (相当于第1-第10条)
     *
     * @return array 对象列表数组
     */
    public static function get($filter = null, $sort = CrudSQL::SQL_ORDER_DEFAULT_ID, $limit = null)
    {
        return self::dao()->get(get_called_class(), $filter, $sort, $limit);
    }

    /**
     * 查询得到单个对象实体
     * @example 示例如下
     *
     *        $blog = Blog::getOne();
     * @param object|string|array $filter 查询条件, 在where后的条件
     * 示例如下:
     *
     *        0. "id = 1, name = 'sky'"
     *        1. array("id = 1", "name = 'sky'")
     *        2. array("id" => "1", "name" => "sky")
     *        3. 允许对象如new User(id = "1", name = "green");
     *
     * 默认:SQL Where条件子语句。如: "( id = 1 and name = 'sky' ) or ( name like '%sky%' )"
     *
     * @param string $sort 排序条件
     * 示例如下:
     *        1. id asc;
     *        2. name desc;
     * @return object 单个对象实体
     */
    public static function getOne($filter = null, $sort = CrudSQL::SQL_ORDER_DEFAULT_ID)
    {
        return self::dao()->getOne(get_called_class(), $filter, $sort);
    }

    /**
     * 根据表ID主键获取指定的对象[ID对应的表列]
     * @example 示例如下
     *
     *        $blog = Blog::getById( 1 );
     * @param string $id 数据对象编号
     * @return 数据对象
     */
    public static function getById($id)
    {
        return self::dao()->getById(get_called_class(), $id);
    }

    /**
     * 数据对象标识最大值
     * @example 示例如下
     *
     *        $max = User::max( "loginTimes" );
     * @param string $column_name 列名, 默认为数据对象标识
     * @param object|string|array $filter 查询条件, 在where后的条件
     * @return int 数据对象标识最大值
     */
    public static function max($column_name = null, $filter = null)
    {
        return DataObjectFunc::max(get_called_class(), $column_name, $filter);
    }

    /**
     * 数据对象指定列名最小值, 如未指定列名, 为标识最小值
     * @example 示例如下
     *
     *        $min = User::min( "loginTimes" );
     * @param string $column_name 列名, 默认为数据对象标识
     * @param object|string|array $filter 查询条件, 在where后的条件
     * @return int 数据对象列名最小值, 如未指定列名, 为标识最小值
     */
    public static function min($column_name = null, $filter = null)
    {
        return DataObjectFunc::min(get_called_class(), $column_name, $filter);
    }

    /**
     * 数据对象指定列名总数
     * @example 示例如下
     *
     *        $sum = User::sum( "loginTimes" );
     * @param string $column_name 列名
     * @param object|string|array $filter 查询条件, 在where后的条件
     * @return int 数据对象列名总数
     */
    public static function sum($column_name = null, $filter = null)
    {
        return DataObjectFunc::sum(get_called_class(), $column_name, $filter);
    }

    /**
     * 对象总计数
     * @example 示例如下
     * 示例如下:
     *
     *        $countBlogs = Blog::count("blog_id>3");
     * @param object|string|array $filter
     *        $filter 格式示例如下:
     *            0. "id = 1, name = 'sky'"
     *            1. array("id = 1", "name = 'sky'")
     *            2. array("id" => "1", "name" => "sky")
     *            3. 允许对象如new User(id = "1", name = "green");
     *
     * 默认:SQL Where条件子语句。如: "( id = 1 and name = 'sky' ) or ( name like '%sky%' )"
     * @return int 对象总计数
     */
    public static function count($filter = null)
    {
        return DataObjectFunc::count(get_called_class(), $filter);
    }

    /**
     * 对象分页
     * @example 示例如下
     *
     *        $blogs = Blog::queryPage( 0, 10,
     *            array(
     *                "(blog_content like '%关键字%' or blog_content like '%公开课%')",
     *                "blog_id<4",
     *                "user_id"=>1
     *            )
     *        );
     * @param int $startPoint  分页开始记录数
     * @param int $endPoint    分页结束记录数
     * @param object|string|array $filter 查询条件, 在where后的条件
     * 示例如下:
     *
     *        0. "id = 1, name = 'sky'"
     *        1. array("id = 1", "name = 'sky'")
     *        2. array("id" => "1", "name" => "sky")
     *        3. 允许对象如new User(id = "1", name = "green");
     *
     * 默认:SQL Where条件子语句。如: "( id = 1 and name = 'sky' ) or ( name like '%sky%' )"
     * @param string $sort 排序条件
     * 默认为 id desc
     *
     * 示例如下:
     *
     *        1. id asc;
     *        2. name desc;
     * @return mixed 对象分页
     */
    public static function queryPage($startPoint, $endPoint, $filter = null, $sort = CrudSQL::SQL_ORDER_DEFAULT_ID)
    {
        return DataObjectFunc::queryPage(get_called_class(), $startPoint, $endPoint, $filter, $sort);
    }

    /**
     * 对象分页根据当前页数和每页显示记录数
     *
     * @example 示例如下
     * 示例如下:
     *
     *        $blogs = Blog::queryPageByPageNo( 1, null, 3 );
     * @param int $pageNo  当前页数
     * @param int $pageSize 每页显示记录数
     * @param object|string|array $filter 查询条件, 在where后的条件
     * 示例如下:
     *
     *        0. "id = 1, name = 'sky'"
     *        1. array("id = 1", "name = 'sky'")
     *        2. array("id" => "1", "name" => "sky")
     *        3. 允许对象如new User(id = "1", name = "green");
     *
     * 默认:SQL Where条件子语句。如: "( id = 1 and name = 'sky' ) or ( name like '%sky%' )"
     *
     * @param string $sort 排序条件
     * 默认为 id desc
     * 示例如下:
     *
     *        1. id asc;
     *        2. name desc;
     *
     * @return array
     *  返回:
     *        - count    : 符合条件的记录总计数
     *        - pageCount: 符合条件的总页数
     *        - data     : 对象分页
     */
    public static function queryPageByPageNo($pageNo, $filter = null, $pageSize = 10, $sort = CrudSQL::SQL_ORDER_DEFAULT_ID)
    {
        return DataObjectFunc::queryPageByPageNo(get_called_class(), $pageNo, $filter, $pageSize, $sort);
    }

    /**
     * 对象总计数[多表关联查询]
     * @example 示例如下
     *
     *        $comments = Comment::countMultitable( "Blog a, Comment b", "b.blog_id = a.blog_id and a.blog_name like '%Web%'" );
     * @param string|array $from 来自多张表或者多个类[必须是数据对象类名], 在from后的多张表名, 表名之间以逗号[,]隔开
     *
     *        示例如下:
     *            0. "table1, table2"
     *            1. array("table1", "table2")
     *            2. "class1, class2"
     *            3. array("class1", "class2")
     *
     * @param object|string|array $filter
     *
     *        $filter 格式示例如下:
     *            0. 允许对象如new User(id = "1", name = "green");
     *            1. "id = 1", "name = 'sky'"
     *            2. array("id = 1", "name = 'sky'")
     *            3. array("id" => "1", "name" => "sky")
     *
     * @return int 对象总计数
     */
    public static function countMultitable($from, $filter = null)
    {
        return DataObjectFunc::countMultitable(get_called_class(), $from, $filter);
    }

    /**
     * 对象分页[多表关联查询]
     *
     * @example 示例如下
     *
     *        $comments = Comment::queryPageMultitable( 1, 6, "Blog a, Comment b", "b.blog_id = a.blog_id and a.blog_name like '%Web%'" );
     * @param int $startPoint  分页开始记录数
     * @param int $endPoint    分页结束记录数
     * @param string|array $from 来自多张表或者多个类[必须是数据对象类名], 在from后的多张表名, 表名之间以逗号[,]隔开
     * 示例如下:
     *
     *        0. "table1, table2"
     *        1. array("table1", "table2")
     *        2. "class1, class2"
     *        3. array("class1", "class2")
     *
     * @param object|string|array $filter 查询条件, 在where后的条件
     * 示例如下:
     *
     *        0. "id = 1, name = 'sky'"
     *        1. array("id = 1", "name = 'sky'")
     *        2. array("id" => "1", "name" => "sky")
     *        3. 允许对象如new User(id = "1", name = "green");
     *
     * 默认:SQL Where条件子语句。如: "( id = 1 and name = 'sky' ) or ( name like '%sky%' )"
     *
     * @param string $sort 排序条件
     * 默认为 id desc
     * 示例如下:
     *
     *        1. id asc;
     *        2. name desc;
     * @return mixed 对象分页
     */
    public static function queryPageMultitable($startPoint, $endPoint, $from, $filter = null, $sort = CrudSQL::SQL_ORDER_DEFAULT_ID)
    {
        return DataObjectFunc::queryPageMultitable(get_called_class(), $startPoint, $endPoint, $from, $filter, $sort);
    }

    /**
     * 查询数据对象列表[多表关联查询]
     *
     * @example 示例如下
     *
     *        $comments = Comment::getMultitable( "Blog a, Comment b", "b.blog_id = a.blog_id and a.blog_name like '%Web%'" );
     * @param string|array $from 来自多张表或者多个类[必须是数据对象类名], 在from后的多张表名, 表名之间以逗号[,]隔开
     * 示例如下:
     *
     *        0. "table1, table2"
     *        1. array("table1", "table2")
     *        2. "class1, class2"
     *        3. array("class1", "class2")
     *
     * @param object|string|array $filter 查询条件, 在where后的条件
     * 示例如下:
     *
     *        0. "id = 1, name = 'sky'"
     *        1. array("id = 1", "name = 'sky'")
     *        2. array("id" => "1", "name" => "sky")
     *        3. 允许对象如new User(id = "1", name = "green");
     *
     * 默认:SQL Where条件子语句。如: "( id = 1 and name = 'sky' ) or ( name like '%sky%' )"
     *
     * @param string $sort 排序条件
     * 默认为 id desc
     * 示例如下:
     *
     *        1. id asc;
     *        2. name desc;
     * @return mixed 对象分页
     */
    public static function getMultitable($from, $filter = null, $sort = CrudSQL::SQL_ORDER_DEFAULT_ID)
    {
        return DataObjectFunc::getMultitable(get_called_class(), $from, $filter, $sort);
    }
    //</editor-fold>

    //<editor-fold defaultstate="collapsed" desc="数据类型转换">
    /**
     * 将数据对象转换成xml
     * @example 示例如下
     * 示例如下:
     *
     *         $blog = Blog::getById( 1 );
     *
     *         print_r( $blog->toXml() );
     * @param $filterArray 需要过滤不生成的对象的field
     *
     * 示例: $filterArray = array("id", "commitTime");
     *
     * @param $isAll 是否对象所有的field都要生成, 包括没有内容或者内容为空的field
     * @return string xml内容
     */
    public function toXml($isAll = true, $filterArray = null)
    {
        return UtilObject::object_to_xml($this, $filterArray, $isAll);
    }

    /**
     * 将数据对象转换成Json类型格式
     * @example 示例如下
     * 示例如下:
     *
     *         $blog = Blog::getById( 1 );
     *
     *         print_r( $blog->toJson() );
     * @param $isAll 是否对象所有的field都要生成, 包括没有内容或者内容为空的field
     * @return string Json格式的数据格式的字符串。
     */
    public function toJson($isAll = false)
    {
        return DataObjectFunc::toJson($this, $isAll);
    }

    /**
     * 将数据对象转换成Array
     * @example 示例如下
     * 示例如下:
     *
     *         $blog = Blog::getById( 1 );
     *
     *         print_r( $blog->toArray() );
     * @param $isAll 是否对象所有的field都要生成, 包括没有内容或者内容为空的field
     * @return array 数组
     */
    public function toArray($isAll = true)
    {
        return UtilObject::object_to_array($this, $isAll);
    }
    //</editor-fold>
}
