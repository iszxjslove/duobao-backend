<?php

namespace app\admin\model\user;

use think\Model;

/**
 * Class Money
 * @package app/admin/model/user
 * @property int id 
 * @property int user_id 会员ID
 * @property float money 变更余额
 * @property float before 变更前余额
 * @property float after 变更后余额
 * @property string memo 备注
 * @property int createtime 创建时间
 */
class Money extends Model
{

    

    

    // 表名
    protected $name = 'user_money_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







}
