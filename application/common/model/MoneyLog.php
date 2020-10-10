<?php

namespace app\common\model;

use think\Model;

/**
 * 会员余额日志模型
 * @property int id 
 * @property int user_id 会员ID
 * @property float money 变更余额
 * @property float before 变更前余额
 * @property float after 变更后余额
 * @property string memo 备注
 * @property int createtime 创建时间
 */
class MoneyLog Extends Model
{

    // 表名
    protected $name = 'user_money_log';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
        'create_time_text'
    ];

    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['createtime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }
}
