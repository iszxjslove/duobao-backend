<?php


namespace app\common\model;


use fast\Random;
use think\Model;

/**
 * Class RechargeOrder
 * @package app\common\model
 * @method getByTradeNo($trade_no)
 * @property int id 充值订单ID
 * @property int user_id 用户ID
 * @property string trade_no 订单号
 * @property float amount 订单金额
 * @property string merchant_config 商户配置
 * @property string other_params 其它参数
 * @property int create_time 创建时间
 * @property int first_recharge 首充
 * @property int completion_time 完成时间
 * @property int update_time 更新时间
 * @property int status 状态
 */
class RechargeOrder extends Base
{
    protected $name = 'recharge_order';
    protected $autoWriteTimestamp = 'init';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $append = ['create_time_text', 'update_time_text', 'completion_time_text'];
    protected $insert = ['status' => 0];

    protected static function init()
    {
        self::beforeUpdate(static function ($row) {
            if ($row->status === 1) {
                $row->completion_time = time();
            }
        });
        self::afterWrite(static function ($row) {
            if ($row->status === 1 && $row->first_recharge) {
                $user = User::get($row->user_id);
                if ($user) {
                    $user->first_recharge_time = $row->completion_time;
                    $user->save();
                }
            }
        });
    }

    public function createOrder($uid, $amount, $merchant = [], $other_params = [], $extend = [])
    {
        $order = array_merge([
            'user_id'         => $uid,
            'trade_no'        => '10' . time() . Random::numeric(4),
            'amount'          => $amount,
            'merchant_config' => $merchant,
            'other_params'    => $other_params,
            'fastpay_name'    => $merchant['fastpay']
        ], $extend);
        $this->save($order);
        return $this;
    }

    protected function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: $data['create_time'] ?? '';
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ?: $data['update_time'] ?? '';
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function getCompletionTimeTextAttr($value, $data)
    {
        $value = $value ?: $data['update_time'] ?? '';
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setMerchantConfigAttr($value)
    {
        return $value && is_array($value) ? json_encode($value) : $value;
    }

    protected function getMerchantConfigAttr($value)
    {
        return $value ? json_decode($value, true) : $value;
    }

    protected function setOtherParamsAttr($value)
    {
        return $value && is_array($value) ? json_encode($value) : $value;
    }

    protected function getOtherParamsAttr($value)
    {
        return $value ? json_decode($value, true) : $value;
    }
}