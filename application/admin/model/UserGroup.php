<?php

namespace app\admin\model;

use think\Model;



/**
 * Class UserGroup
 * @package app/admin/model
 * @property int id 
 * @property string name 组名
 * @property string rules 权限节点
 * @property int createtime 添加时间
 * @property int updatetime 更新时间
 * @property int is_default 注册默认分组
 * @property int is_test 测试用户组
 * @property string status 状态
 */
class UserGroup extends Model
{

    // 表名
    protected $name = 'user_group';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'status_text'
    ];

    protected static function init()
    {
        self::beforeWrite(static function ($row) {
            $row->is_default = $row->is_default ? 1 : 0;
            if ($row->is_default) {
                self::where(['is_default' => 1])->update(['is_default' => 0]);
            }
        });
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

}
