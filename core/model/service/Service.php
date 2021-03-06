<?php

/**
 * -----------| 所有Service的父类 |-----------
 * @category Betterlife
 * @package core.model
 * @subpackage service
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
class Service extends BBObject
{
    /**
     * @var IDao 当前使用的数据库调用对象
     */
    private static $currentDao;
    protected static function dao()
    {
        if (empty(self::$currentDao)) {
            self::$currentDao = ManagerDb::newInstance()->dao();
        }
        return self::$currentDao;
    }

    /**
     * 获取数据对象属性映射表字段意义
     * @param string $dataobject 当前数据对象
     * 可设定对象未定义的成员变量[但不建议这样做]
     * @return array 表列名列表；键:列名,值:列注释说明
     */
    public static function fieldsMean($tablename)
    {
        return ManagerDb::newInstance()->dbinfo()->fieldMapNameList($tablename);
    }

    /**
     * 将过滤条件转换成需查询的模糊条件
     * @param array|object $filter 过滤条件
     * @return string 查询条件
     */
    protected function filtertoCondition($filter)
    {
        if (is_array($filter)) {
            $condition = $filter;
        } elseif (is_object($filter)) {
            $condition = UtilObject::object_to_array($filter);
        }
        if (!empty($condition) && (count($condition) > 0 )) {
            $conditionArr = array();
            foreach ($condition as $key => $value) {
                if (empty($value) && $value !== 0 && $value !== '0') {
                    continue;
                }
                if (!UtilString::is_utf8($value)) {
                    $value = UtilString::gbk2utf8($value);
                }
                if (is_int($value) || is_bool($value)) {
                    $conditionArr[] = $key . "='" . $value . "'";
                } elseif (contain($value, "T00:00:00")) {
                    $value = str_replace("T00:00:00", "", $value);
                    $conditionArr[] = $key . "='" . $value . "'";
                } else {
                    if (is_numeric($value)) {
                        $judgeKey = strtolower($key);
                        if (contains($judgeKey, array("type", "stat"))) {//如果是枚举类型
                            $conditionArr[] = $key . "='" . $value . "'";
                            continue;
                        }
                    }

                    $where_clause_one = "(";
                    $search_atom  = explode(" ", trim($value));
                    $search_key = $key;
                    array_walk($search_atom, function (&$value, $key, $search_key) {
                        $value = " ( $search_key LIKE '%" . $value . "%' ) ";
                    }, $search_key);
                    $where_clause_one .= implode(" and ", $search_atom);
                    $where_clause_one .= ")";

                    $conditionArr[] = $where_clause_one;
                    // $conditionArr[] = $key . " like '%" . $value . "%'";
                }
            }
            $condition = implode(" and ", $conditionArr);
        } else {
            $condition = "";
        }
        return $condition;
    }

    /**
     * 转换成数组
     * @return int
     */
    public static function toArray()
    {
        $servicename = get_called_class();
        $result      = null;
        $services = array();
        if (class_exists($servicename)) {
            $service    = new ReflectionClass($servicename);
            $methods    = $service->getMethods();
            $methodsArr = array();
            foreach ($methods as $method) {
                if ($method->isPublic()) {
                    $methodname = $method->getName();
                    $params     = $method->getParameters();
                    $paramArr   = array();
                    $count      = 1;
                    foreach ($params as $i => $param) {
                        $paramname = $param->getName();
                        if ($param->isDefaultValueAvailable()) {
                            $paramArr[$paramname]  = $param->getDefaultValue();
                        } else {
                            $paramArr[$paramname] = "无默认值";
                        }
                        $methodsArr[$methodname]  = $paramArr;
                    }
                }
                $services[$servicename] = array('methods' => $methodsArr);
                unset($services[$servicename]['methods']["__set"]);
                unset($services[$servicename]['methods']["__get"]);
            }
        }
        if (count($services) > 0) {
            $result = $services;
        }
        //print_r($result);
        return $result;
    }
}
