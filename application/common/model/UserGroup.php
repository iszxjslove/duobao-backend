<?php

namespace app\common\model;

use think\Model;

/**
 * Class UserGroup
 * @package app/common/model
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
    ];

}
