<?php


namespace app\api\model;


use app\common\model\User;
use think\Config;
use think\Model;

class WithdrawOrder extends Model
{
    protected $name = 'withdraw_order';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = 'update_time';

    protected $append = ['create_time_text', 'update_time_text'];

    public function getCreateTimeTextAttr($value, $data)
    {
        return is_numeric($data['create_time']) ? date('Y-m-d H:i:s', $data['create_time']) : $data['create_time'];
    }

    public function getUpdateTimeTextAttr($value, $data)
    {
        return is_numeric($data['update_time']) ? date('Y-m-d H:i:s', $data['update_time']) : $data['update_time'];
    }

    public function createOrder($user_id, $amount, $card_id)
    {
        $withdraw_rate = Config::get('site.withdraw_rate');
        $rate = 0;
        foreach ($withdraw_rate as $key => $value) {
            if ($amount >= $key) {
                $rate = $value;
            }
        }
        $fee = bcmul($amount, $rate, 2);
        User::money($user_id, -$amount, '提现');
        User::hold_balance($user_id, $amount, '提现预扣');
        return $this->insert([
            'user_id'  => $user_id,
            'admin_id' => 0,
            'card_id'  => $card_id,
            'trade_no' => \NumberPool::center(2)->getOne(),
            'amount'   => $amount,
            'fee'      => $fee
        ]);
    }
}