<?php

namespace app\common\model;

use fast\Tree;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Model;

/**
 * 分类模型
 * @property int id 
 * @property int pid 父ID
 * @property string type 栏目类型
 * @property string name 
 * @property string nickname 
 * @property string flag 
 * @property string image 图片
 * @property string keywords 关键字
 * @property string description 描述
 * @property string diyname 自定义名称
 * @property int createtime 创建时间
 * @property int updatetime 更新时间
 * @property int weigh 权重
 * @property string status 状态
 */
class Category extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'type_text',
        'flag_text',
    ];

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $row->save(['weigh' => $row['id']]);
        });
    }

    public function setFlagAttr($value, $data)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    /**
     * 读取分类类型
     * @return array
     */
    public static function getTypeList()
    {
        $typeList = config('site.categorytype');
        foreach ($typeList as $k => &$v) {
            $v = __($v);
        }
        return $typeList;
    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['type'];
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getFlagList()
    {
        return ['hot' => __('Hot'), 'index' => __('Index'), 'recommend' => __('Recommend')];
    }

    public function getFlagTextAttr($value, $data)
    {
        $value = $value ? $value : $data['flag'];
        $valueArr = explode(',', $value);
        $list = $this->getFlagList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }

    /**
     * 读取分类列表
     * @param string $type   指定类型
     * @param string $status 指定状态
     * @return array
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public static function getCategoryArray($type = null, $status = null, $pid = null)
    {
        return collection((array)(new Category)->where(function ($query) use ($type, $status, $pid) {
            if (!is_null($type)) {
                $query->where('type', '=', $type);
            }
            if (!is_null($status)) {
                $query->where('status', '=', $status);
            }
            if (!is_null($pid)) {
                $query->where('pid', '=', $pid);
            }
        })->order('weigh', 'desc')->select())->toArray();
    }

    /**
     * 获取分类树
     * @param string $type
     * @param string $status
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getCategoryTree($type = null, $status = null)
    {
        $tree = Tree::instance();
        $tree->init(self::getCategoryArray($type, $status), 'pid');
        return $tree->getTreeList($tree->getTreeArray(0), 'name');
    }
}
