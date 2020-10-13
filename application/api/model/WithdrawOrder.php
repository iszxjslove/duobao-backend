<?php


namespace app\api\model;


use app\common\model\Base;
use app\common\model\User;
use think\Config;

/**
 * Class WithdrawOrder
 * @package app/api/model
 * @property int id 提现记录ID
 * @property int user_id 用户ID
 * @property int admin_id 管理员ID
 * @property string trade_no 交易号
 * @property float amount 金额
 * @property float fee 手续费
 * @property float real_amount 实付金额
 * @property float channel_fee 通道手续费
 * @property string card_data 银行卡数据
 * @property string merchant_config 商户参数
 * @property string result_data 结果数据
 * @property int create_time 创建时间
 * @property int update_time 更新时间
 * @property int completion_time 完成时间
 * @property int status 状态
 */
class WithdrawOrder extends Base
{
    protected $name = 'withdraw_order';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = 'update_time';

    protected $append = ['create_time_text', 'update_time_text'];

    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_time'] ?? '');
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['update_time'] ?? '');
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    public function createOrder($user_id, $amount, $card_id)
    {
        $withdraw_rate = Config::get('site.withdraw_rate');
        ksort($withdraw_rate);
        $rate = 0;
        foreach ($withdraw_rate as $key => $value) {
            if ($amount >= $key) {
                $rate = $value;
            }
        }
        $fee = bcmul($amount, bcdiv($rate, 100, 2), 2);
        User::money($user_id, -$amount, '提现');
        User::hold_balance($user_id, $amount, '提现预扣');
        return $this->save([
            'user_id'     => $user_id,
            'admin_id'    => 0,
            'card_id'     => $card_id,
            'trade_no'    => \NumberPool::center(2)->getOne(),
            'amount'      => $amount,
            'fee'         => $fee,
            'real_amount' => bcsub($amount, $fee, 2)
        ]);
    }
}