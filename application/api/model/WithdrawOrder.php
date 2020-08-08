<?php


namespace app\api\model;


use app\common\model\Base;
use app\common\model\User;
use think\Config;

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