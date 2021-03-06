<?php

/**
 * -----------| Delete 删除记录SQL语句 |-----------
 * @category Betterlife
 * @package core.db.sql.util.crud
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
class CrudSqlDelete extends CrudSQL
{
    /**
     * 创建删除从表SQL子语句
     * @param string $tableorclassName 表名|类名[映射表]
     * @return CrudSqlDelete
     */
    public function deletefrom($tableorclassName)
    {
        if (class_exists($tableorclassName)) {
            $this->tableName = ConfigDb::orm($tableorclassName);
        } else {
            $this->tableName = $tableorclassName;
        }
        return $this;
    }

    /**
     * 生成需要的完整的SQL语句
     * @return string SQL完整的语句
     */
    public function result()
    {
        $this->query = self::SQL_DELETE . self::SQL_FROM . $this->tableName;
        if (!empty($this->whereClause)) {
            $this->query .= self::SQL_WHERE . $this->whereClause;
        }
        return $this->query;
    }
}
