<?php

/**
 * -----------| 对象列表容器 |-----------
 *
 * 参考java.util.ArrayList的方法命名定义
 *
 * 改造成为对象列表容器。
 * @category Betterlife
 * @package core.model
 * @author skygreen2001 <skygreen2001@gmail.com>
 */
class DataObjectList extends ArrayObject implements IteratorAggregate
{
    public function __construct($array = null)
    {
        if (empty($array)) {
            $array = array();
        }
        parent::__construct($array);
    }

    /**
     * 获取列表所有对象的ID列表
     *
     * 对象按规范必需有ID字段
     * @return array
     */
    public function getIdList()
    {
        if ($this->count() > 0) {
            $object  = $this[0];
            $id_name = DataObjectSpec::getRealIDColumnName($object);
            $list    = array();
            foreach ($this as $item) {
                $list[$item->$id_name] = $item->$id_name;
            }
        } else {
            $list = null;
        }
        return $list;
    }

    /**
     * @param value
     */
    public function add($value)
    {
        parent::append($value);
    }

    /**
     * 移除列表指定索引的对象
     * @param int $index
     */
    public function remove($index)
    {
        unset($this[$index]);
    }

    /**
     * 查看列表指定索引的对象
     *
     * 索引从0开始
     * @param int $index
     */
    public function get($index)
    {
        return $this[$index];
    }

    /**
     * 清空所有的对象，可看作初始化
     */
    public function clear()
    {
        $this->exchangeArray(array());
        reset($this);
    }

    /**
     * 返回对象的个数
     */
    public function size()
    {
        return parent::count();
    }

    public function isEmpty()
    {
        return $this->size() == 0;
    }

    /**
     * Create a new iterator from an ArrayObject instance
     * @return ArrayIterator — An iterator from an ArrayObject.
     * @throws Exception — on failure.
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this);
    }

    //<editor-fold defaultstate="collapsed" desc="数据类型转换">
    /**
     * 在Flex框架里无法辨认DataObjectList对象；需要转化成数组
     * @return array
     */
    public function flex()
    {
        return iterator_to_array($this->getIterator(), true);
    }
    /**
     * 转换成数组
     */
    public function toArray()
    {
        return iterator_to_array($this->getIterator(), true);
    }
    /**
     * 转换成xml文档
     * @return string xml文档
     */
    public function toXml()
    {
        if ($this->count() > 0) {
            $object  = $this[0];
            $id_name = DataObjectSpec::getRealIDColumnName($object);
            $dataobjectsArr = array();
            foreach ($this as $dataobject) {
                $dataobjectArr = $dataobject->toArray();
                $dataobjectsArr[$dataobject->$id_name] = $dataobjectArr;
            }
            $objectname = $object->classname();
            $objectname = lcfirst($objectname);
            $result     = UtilArray::array_to_xml($dataobjectsArr, $objectname . "s");
        } else {
            $result = null;
        }
        return $result;
    }
    /**
     * 转换成xml文档
     * @return string xml文档
     */
    public function toJson()
    {
        if ($this->count() > 0) {
            $object  = $this[0];
            $id_name = DataObjectSpec::getRealIDColumnName($object);
            $dataobjectsArr = array();
            foreach ($this as $dataobject) {
                $dataobjectArr = $dataobject->toArray();
                $dataobjectsArr[$dataobject->$id_name] = $dataobjectArr;
            }
            $objectname     = $object->classname();
            $objectname     = lcfirst($objectname);
            $dataobjectsArr = array($objectname => $dataobjectsArr);
            $result = json_encode($dataobjectsArr);
        } else {
            $result = null;
        }
        return $result;
    }
    //</editor-fold>
}
