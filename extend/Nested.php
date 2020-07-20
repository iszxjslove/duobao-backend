<?php

use think\Collection;
use think\Db;
use think\db\exception\BindParamException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;

class Nested
{
    /**
     * @var string 表名
     */
    private $tableName;

    /**
     * @var string 左键
     */
    private $leftKey = "left_key";

    /**
     * @var string 右键
     */
    private $rightKey = "right_key";

    /**
     * @var string 父亲字段
     */
    private $parentKey = "parent_id";

    /**
     * @var string 节点深度
     */
    private $levelKey = "level";

    /**
     * @var string 主键
     */
    private $primaryKey = "id";

    /**
     * @var string 多根节点的根节点字段名
     */
    private $multi = "root_id";

    /**
     * @var null 错误信息
     */
    private $_error = null;

    /**
     * @var array 节点的缓存
     */
    private static $itemCache = [];

    /**
     * NestedSets constructor.
     * @param $dbTarg mixed 数据表名或者模型对象
     * @param null $leftKey
     * @param null $rightKey
     * @param null $parentKey
     * @param null $levelKey
     * @param null $primaryKey
     * @throws Exception
     */
    public function __construct($dbTarg, $leftKey = null, $rightKey = null, $parentKey = null, $levelKey = null, $primaryKey = null)
    {
        //如果是表名则处理配置
        if (is_string($dbTarg)) {
            $this->tableName = $dbTarg;
        }

        //允许传入模型对象
        if (is_object($dbTarg)) {
            if (method_exists($dbTarg, 'getTable')) {
                throw new Exception('不能传入该对象');
            }

            $this->tableName = $dbTarg->getTable();
            if (property_exists($dbTarg, 'nestedConfig') && is_array($dbTarg->nestedConfig)) {
                isset($dbTarg->nestedConfig['leftKey']) && $this->leftKey = $dbTarg->nestedConfig['leftKey'];
                isset($dbTarg->nestedConfig['rightKey']) && $this->rightKey = $dbTarg->nestedConfig['rightKey'];
                isset($dbTarg->nestedConfig['parentKey']) && $this->parentKey = $dbTarg->nestedConfig['parentKey'];
                isset($dbTarg->nestedConfig['primaryKey']) && $this->primaryKey = $dbTarg->nestedConfig['primaryKey'];
                isset($dbTarg->nestedConfig['levelKey']) && $this->levelKey = $dbTarg->nestedConfig['levelKey'];
            }
        }

        //构造方法中传入的配置会覆盖其他方式的配置
        isset($leftKey) && $this->leftKey = $leftKey;
        isset($rightKey) && $this->rightKey = $rightKey;
        isset($parentKey) && $this->parentKey = $parentKey;
        isset($primaryKey) && $this->primaryKey = $primaryKey;
        isset($levelKey) && $this->levelKey = $levelKey;
    }

    /**
     * 统计下级
     * @param int $id 要统计的ID
     * @param int $depth 统计深度
     * @param bool $itself 包含本身
     * @return bool|int|string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public function count($id, $depth = 0, $itself = false)
    {
        $row = Db::table($this->tableName)->where($this->primaryKey, $id)->find();
        if (!$row) {
            return false;
        }
        $condition = [
            $this->leftKey  => ['>=', $row[$this->leftKey]],
            $this->rightKey => ['<=', $row[$this->rightKey]]
        ];
        if ($depth) {
            $condition[$this->levelKey] = ['<=', $row[$this->levelKey] + $depth];
        }
        $count = Db::table($this->tableName)->where($condition)->count();
        if ($itself === false) {
            return $count - 1;
        }
        return $count;
    }

    /**
     * 获取整棵树
     * @return bool|false|string|Collection
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function getTree()
    {
        return Db::table($this->tableName)->order((string)($this->leftKey))->select();
    }

    /**
     * 获取当前节点的所有分支节点|不包含当前节点
     * @param $id
     * @param string $optionOne
     * @param string $optionTwo
     * @return bool|false|string|Collection
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public function getBranch($id, $optionOne = '>', $optionTwo = '<')
    {
        $item = $this->getItem($id);
        if (!$item) {
            throw new Exception('没有该节点');
        }

        $condition = [
            $this->leftKey  => [$optionOne, $item[$this->leftKey]],
            $this->rightKey => [$optionTwo, $item[$this->rightKey]]
        ];

        return Db::table($this->tableName)
            ->where($condition)
            ->order((string)($this->leftKey))
            ->select();
    }

    /**
     * 获取当前节点的所有分支节点 | 包含当前节点
     * @param $id
     * @return bool|false|string|Collection
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public function getPath($id)
    {
        return $this->getBranch($id, ">=", "<=");
    }

    /**
     * 获取该节点的所有子节点 | 注意是子节点，不包含孙节点等
     * @param $id
     * @return bool|false|string|Collection
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getChild($id)
    {
        return Db::table($this->tableName)
            ->where($this->parentKey, '=', $id)
            ->order((string)($this->leftKey))
            ->select();
    }

    /**
     * 添加新节点
     * @param $parentId
     * @param array $data
     * @param string $position
     * @return bool
     */
    public function insert($parentId, array $data = [], $position = "top")
    {
        $parent = $this->getItem($parentId);

        if (!$parent) {
            $parentId = 0;
            $level = 1;
            if ($position === "top") {
                $key = 1;
            } else {
                $key = Db::table($this->tableName)
                        ->max((string)($this->rightKey)) + 1;
            }
        } else {
            $key = ($position === "top") ? $parent[$this->leftKey] + 1 : $parent[$this->rightKey];
            $level = $parent[$this->levelKey] + 1;
        }

        Db::startTrans();
        //更新其他节点
        $sql = "UPDATE {$this->tableName} SET {$this->rightKey} = {$this->rightKey}+2,{$this->leftKey} = IF({$this->leftKey}>={$key},{$this->leftKey}+2,{$this->leftKey}) WHERE {$this->rightKey}>={$key}";
        try {
            Db::table($this->tableName)
                ->query($sql);

            $newNode[$this->parentKey] = $parentId;
            $newNode[$this->leftKey] = $key;
            $newNode[$this->rightKey] = $key + 1;
            $newNode[$this->levelKey] = $level;
            $tmpData = array_merge($newNode, $data);

            $id = Db::table($this->tableName)->insertGetId($tmpData);
            Db::commit();
            return $id;
        } catch (Exception $e) {
            Db::rollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 删除某个节点   包含了该节点的后代节点
     * @param $id
     * @return bool
     * @throws Exception
     */
    public function delete($id)
    {
        $item = $this->getItem($id);
        if (!$item) {
            throw new Exception('没有该节点');
        }

        $keyWidth = $item[$this->rightKey] - $item[$this->leftKey] + 1;

        //先删除节点及后代节点
        $condition[] = [$this->leftKey, '>=', $item[$this->leftKey]];
        $condition[] = [$this->rightKey, '<=', $item[$this->rightKey]];

        try {
            Db::table($this->tableName)
                ->where($condition)->delete();

            $sql = "UPDATE {$this->tableName} SET {$this->leftKey} = IF({$this->leftKey}>{$item[$this->leftKey]}, {$this->leftKey}-{$keyWidth}, {$this->leftKey}), {$this->rightKey} = {$this->rightKey}-{$keyWidth} WHERE {$this->rightKey}>{$item[$this->rightKey]}";
            //再移动节点
            Db::table($this->tableName)->query($sql);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 将一个节点移动到另个一节点下
     * @param $id
     * @param $parentId
     * @param string $position bottom表示在后边插入   top表示开始插入
     * @return bool
     * @throws Exception
     */
    public function moveUnder($id, $parentId, $position = "bottom")
    {
        $item = $this->getItem($id);
        if (!$item) {
            throw new Exception('没有该节点');
        }

        $parent = $this->getItem($parentId);

        if (!$parent) {
            $level = 1;
            // 在顶部插入
            if ($position === 'top') {
                $nearKey = 0;
            } else {
                // 选择最大的右键作为开始
                $nearKey = Db::table($this->tableName)
                    ->max("{$this->rightKey}");
            }
        } else {
            $level = $parent[$this->levelKey] + 1;
            if ($position === 'top') {
                $nearKey = $parent[$this->leftKey];
            } else {
                //若在底部插入则起始键为父节点的右键减1
                $nearKey = $parent[$this->rightKey] - 1;
            }
        }

        return $this->move($id, $parentId, $nearKey, $level);
    }

    /**
     * 把主键为id的节点移动到主键为nearId的节点的前或者后
     * @param $id
     * @param $nearId
     * @param string $position
     * @return bool
     * @throws Exception
     */
    public function moveNear($id, $nearId, $position = 'after')
    {
        $item = $this->getItem($id);
        if (!$item) {
            throw new Exception("要移动的节点不存在");
        }

        $near = $this->getItem($nearId);
        if (!$near) {
            throw new Exception("附近的节点不存在");
        }

        $level = $near[$this->levelKey];

        //根据要移动的位置选择键
        if ($position === 'before') {
            $nearKey = $near[$this->leftKey] - 1;
        } else {
            $nearKey = $near[$this->rightKey];
        }

        //移动节点
        return $this->move($id, $near[$this->parentKey], $nearKey, $level);
    }

    private function setError($msg)
    {
        $this->_error = $msg;
    }

    public function getError()
    {
        return $this->_error ?: '';
    }

    /**
     * 移动节点
     * @param $id
     * @param $parentId
     * @param $nearKey
     * @param $level
     * @return bool
     * @throws BindParamException
     * @throws \think\exception\PDOException
     */
    private function move($id, $parentId, $nearKey, $level)
    {
        $item = $this->getItem($id);

        //检查能否移动该节点若为移动到节点本身下则返回错误
        if ($nearKey >= $item[$this->leftKey] && $nearKey <= $item[$this->rightKey]) {
            return false;
        }

        $keyWidth = $item[$this->rightKey] - $item[$this->leftKey] + 1;
        $levelWidth = $level - $item[$this->levelKey];

        if ($item[$this->rightKey] < $nearKey) {
            $treeEdit = $nearKey - $item[$this->leftKey] + 1 - $keyWidth;
            $sql = "UPDATE {$this->tableName} 
                    SET 
                    {$this->leftKey} = IF(
                        {$this->rightKey} <= {$item[$this->rightKey]},
                        {$this->leftKey} + {$treeEdit},
                        IF(
                            {$this->leftKey} > {$item[$this->rightKey]},
                            {$this->leftKey} - {$keyWidth},
                            {$this->leftKey}
                        )
                    ),
                    {$this->levelKey} = IF(
                        {$this->rightKey} <= {$item[$this->rightKey]},
                        {$this->levelKey} + {$levelWidth},
                        {$this->levelKey}
                    ),
                    {$this->rightKey} = IF(
                        {$this->rightKey} <= {$item[$this->rightKey]},
                        {$this->rightKey} + {$treeEdit},
                        IF(
                            {$this->rightKey} <= {$nearKey},
                            {$this->rightKey} - {$keyWidth},
                            {$this->rightKey}
                        )
                    ),
                    {$this->parentKey} = IF(
                        {$this->primaryKey} = {$id},
                        {$parentId},
                        {$this->parentKey}
                    )
                    WHERE 
                    {$this->rightKey} > {$item[$this->leftKey]}
                    AND 
                    {$this->leftKey} <= {$nearKey}";
            Db::table($this->tableName)->query($sql);
        } else {
            $treeEdit = $nearKey - $item[$this->leftKey] + 1;

            $sql = "UPDATE {$this->tableName}
                    SET 
                    {$this->rightKey} = IF(
						{$this->leftKey} >= {$item[$this->leftKey]},
						{$this->rightKey} + {$treeEdit},
						IF(
							{$this->rightKey} < {$item[$this->leftKey]},
							{$this->rightKey} + {$keyWidth},
							{$this->rightKey}
						)
					),
					{$this->levelKey} = IF(
						{$this->leftKey} >= {$item[$this->leftKey]},
						{$this->levelKey} + {$levelWidth},
						{$this->levelKey}
					),
					{$this->leftKey} = IF(
						{$this->leftKey} >= {$item[$this->leftKey]},
						{$this->leftKey} + {$treeEdit},
						IF(
							{$this->leftKey} > {$nearKey},
							{$this->leftKey} + {$keyWidth},
							{$this->leftKey}
						)
					),
					{$this->parentKey} = IF(
						{$this->primaryKey} = {$id},
						{$parentId},
						{$this->parentKey}
					)
					WHERE
					{$this->rightKey} > {$nearKey}
					AND
					{$this->leftKey} < {$item[$this->rightKey]}";
            Db::table($this->tableName)->query($sql);
        }

        return true;

    }

    /**
     * 根据ID获取某个节点
     * @param $id
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    private function getItem($id)
    {
        if (!isset(self::$itemCache[$id])) {
            self::$itemCache[$id] =
                Db::table($this->tableName)
                    ->field([$this->leftKey, $this->rightKey, $this->parentKey, $this->levelKey])
                    ->where($this->primaryKey, '=', $id)
                    ->find();
        }

        return self::$itemCache[$id];
    }

}