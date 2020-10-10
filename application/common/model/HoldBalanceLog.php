<?php


namespace app\common\model;


use think\Model;



/**
 * Class HoldBalanceLog
 * @package app/common/model
 * @property int id 
 * @property int user_id 会员ID
 * @property float money 变更余额
 * @property float before 变更前余额
 * @property float after 变更后余额
 * @property string memo 备注
 * @property int createtime 创建时间
 */
class HoldBalanceLog extends Model
{
    // 表名
    protected $name = 'user_hold_balance_log';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
    ];
}