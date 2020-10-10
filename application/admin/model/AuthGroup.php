<?php

namespace app\admin\model;

use think\Model;



/**
 * Class AuthGroup
 * @package app/admin/model
 * @property int id 
 * @property int pid 父组别
 * @property string name 组名
 * @property string rules 规则ID
 * @property int createtime 创建时间
 * @property int updatetime 更新时间
 * @property string status 状态
 */
class AuthGroup extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function getNameAttr($value, $data)
    {
        return __($value);
    }

}
