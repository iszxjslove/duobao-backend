<?php


namespace app\common\model;


use fast\Random;
use think\Model;

/**
 * Class RechargeOrder
 * @package app\common\model
 * @method getByTradeNo($trade_no)
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
        self::beforeUpdate(static function($row){
            if($row->status === 1){
                $row->completion_time = time();
            }
        });
    }

    public function createOrder($uid, $amount, $merchant = [], $other_params = [])
    {
        $order = [
            'user_id'         => $uid,
            'trade_no'        => '10' . time() . Random::alnum(4),
            'amount'          => $amount,
            'merchant_config' => $merchant,
            'other_params'    => $other_params
        ];
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