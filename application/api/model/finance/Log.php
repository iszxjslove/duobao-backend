<?php

namespace app\api\model\finance;

use think\Model;

/**
 * Class Log
 * @package app/api/model/finance
 * @property int id 
 * @property int user_id 会员ID
 * @property float money 变更余额
 * @property float before 变更前余额
 * @property float after 变更后余额
 * @property string memo 备注
 * @property int create_time 创建时间
 */
class Log extends Model
{


    // 表名
    protected $name = 'user_finance_money_log';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text'
    ];


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function user()
    {
        return $this->belongsTo('app\admin\model\user', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
