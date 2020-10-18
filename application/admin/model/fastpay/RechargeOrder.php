<?php

namespace app\admin\model\fastpay;

use app\admin\model\User;
use think\Model;




/**
 * Class RechargeOrder
 * @package app/admin/model/fastpay
 * @property int id 充值订单ID
 * @property int user_id 用户ID
 * @property string trade_no 订单号
 * @property float amount 订单金额
 * @property string merchant_config 商户配置
 * @property string other_params 其它参数
 * @property int create_time 创建时间
 * @property int completion_time 完成时间
 * @property int update_time 更新时间
 * @property int status 状态
 */
class RechargeOrder extends Model
{


    // 表名
    protected $name = 'recharge_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text',
        'completion_time_text',
        'update_time_text'
    ];

    protected static function init()
    {
        self::beforeUpdate(static function ($row) {
            if ($row->status === 1) {
                $row->completion_time = time();
            }
        });
        self::afterWrite(static function ($row) {
            if ($row->status === 1 && $row->first_recharge) {
                $user = \app\common\model\User::get($row->user_id);
                if ($user) {
                    $user->first_recharge_time = $row->completion_time;
                    $user->save();
                }
            }
        });
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCompletionTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['completion_time']) ? $data['completion_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['update_time']) ? $data['update_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setCompletionTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id', '', 'LEFT')->setEagerlyType(0);
    }
}
