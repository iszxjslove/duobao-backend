<?php

namespace app\common\model;

use think\Model;



/**
 * Class UserRule
 * @package app/common/model
 * @property int id 
 * @property int pid 父ID
 * @property string name 名称
 * @property string title 标题
 * @property string remark 备注
 * @property int ismenu 是否菜单
 * @property int createtime 创建时间
 * @property int updatetime 更新时间
 * @property int weigh 权重
 * @property string status 状态
 */
class UserRule extends Model
{

    // 表名
    protected $name = 'user_rule';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];

}
