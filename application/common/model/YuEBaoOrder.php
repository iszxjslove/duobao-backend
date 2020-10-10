<?php


namespace app\common\model;


use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Model;

/**
 * Class YuEBaoOrder
 * @package app/common/model
 * @property int id 金融订单ID
 * @property int user_id 用户ID
 * @property float principal_amount 本金金额
 * @property float remaining_principal_amount 剩余本金金额
 * @property float profit 收益
 * @property int profit_unit 收益单位
 * @property int next_interest_time 下一次计息时间
 * @property string interest_where 利息去向
 * @property string interest_method 计息方式
 * @property string principal_where 本金去向
 * @property string period 周期
 * @property string period_unit 周期单位
 * @property float sum_interest 总利息
 * @property int expiry_time 到期时间
 * @property int end_time 终止时间
 * @property int update_time 更新时间
 * @property int create_time 创建时间
 * @property int status 状态 1 计息中 2 已结束
 * @method static self where($field, $op = null, $condition = null)
 * @property int id 金融订单ID
 * @property int user_id 用户ID
 * @property float principal_amount 本金金额
 * @property float remaining_principal_amount 剩余本金金额
 * @property float profit 收益
 * @property int profit_unit 收益单位
 * @property int next_interest_time 下一次计息时间
 * @property string interest_where 利息去向
 * @property string interest_method 计息方式
 * @property string principal_where 本金去向
 * @property string period 周期
 * @property string period_unit 周期单位
 * @property float sum_interest 总利息
 * @property int expiry_time 到期时间
 * @property int end_time 终止时间
 * @property int update_time 更新时间
 * @property int create_time 创建时间
 * @property int status 状态 1 计息中 2 已结束
 */
class YuEBaoOrder extends Model
{
    protected $name = 'yuebao_order';

    protected $autoWriteTimestamp = 'int';

    protected $createTim = 'create_time';

    protected $updateTime = 'update_time';

    /**
     * 转入余额宝
     * @param YuEBaoProducts $product
     * @param $uid
     * @param $principal
     * @return YuEBaoOrder
     * @throws Exception
     * @throws DbException
     */
    public static function transferIn(YuEBaoProducts $product, $uid, $principal): YuEBaoOrder
    {
        $now_time = time();
        $next_interest_time = 0;
        switch ($product->interest_method) {
            case 'fixed':
                $next_interest_time = strtotime("+{$product->period} {$product->period_unit}", $now_time);
                break;
            case 'day':
                $next_interest_time = $now_time + 86400;
                break;
        }
        $data = [
            'user_id'                    => $uid,       // 用户ID
            'principal_amount'           => $principal, // 本金金额
            'remaining_principal_amount' => $principal, // 剩余本金金额
            'profit'                     => $product->profit,     // 收益
            'profit_unit'                => $product->profit_unit,     // 收益单位
            'next_interest_time'         => $next_interest_time,     // 下一次计息时间 次日计息
            'interest_where'             => $product->interest_where,     // 利息去向
            'interest_method'            => $product->interest_method,     // 计息方式
            'principal_where'            => $product->principal_where,     // 本金去向
            'period'                     => $product->period,     // 周期
            'period_unit'                => $product->period_unit,     // 周期单位
            'sum_interest'               => 0,     // 总利息
            'expiry_time'                => strtotime("+{$product->period} {$product->period_unit}"),     // 到期时间
            'end_time'                   => '',     // 终止时间
            'status'                     => 1
        ];
        YuEBao::balance($uid, $principal, 'transfer in');
        return self::create($data);
    }

    /**
     * @param $uid
     * @param $money
     * @param $memo
     * @throws DbException
     * @throws Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public static function transferOut($uid, $money, $memo)
    {
        /* @var self $order */
        $order = self::where(['user_id' => $uid, 'status' => ['gt', 0]])->order('status desc')->find();
        if ($money <= 0) {
            return false;
        }
        if (!$order) {
            throw new Exception('Insufficient Balance');
        }
        $after = 0;
        $change = $money;
        if ($order->remaining_principal_amount <= $money) {
            $change = $order->remaining_principal_amount;
            $after = function_exists('bcsub') ? bcsub($money, $change, 2) : $money - $change;
            $order->remaining_principal_amount = 0;
            $order->status = 0;
            $order->end_time = time();
        } else {
            $order->remaining_principal_amount -= $money;
        }
        YuEBao::balance($uid, -$change, $memo);
        $result = $order->save();
        if ($after > 0) {
            return self::transferOut($uid, $after, $memo);
        }
        return $result;
    }

    protected function getCreateTimeAttr($value)
    {
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value && is_string($value) ? strtotime($value) : $value;
    }

    protected function getUpdateTimeAttr($value)
    {
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value && is_string($value) ? strtotime($value) : $value;
    }

    protected function getExpiryTimeAttr($value)
    {
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    protected function setExpiryTimeAttr($value)
    {
        return $value && is_string($value) ? strtotime($value) : $value;
    }

    protected function getEndTimeAttr($value)
    {
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    protected function setEndTimeAttr($value)
    {
        return $value && is_string($value) ? strtotime($value) : $value;
    }

    protected function getFirstInterestTimeAttr($value)
    {
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    protected function setFirstInterestTimeAttr($value)
    {
        return $value && is_string($value) ? strtotime($value) : $value;
    }

    protected function getNextInterestTimeAttr($value)
    {
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    protected function setNextInterestTimeAttr($value)
    {
        return $value && is_string($value) ? strtotime($value) : $value;
    }

    protected function getLastInterestTimeAttr($value)
    {
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    protected function setLastInterestTimeAttr($value)
    {
        return $value && is_string($value) ? strtotime($value) : $value;
    }
}