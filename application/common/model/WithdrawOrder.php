<?php


namespace app\common\model;


use app\admin\model\Admin;
use app\admin\model\User;
use fast\Random;
use think\Exception;
use think\exception\DbException;
use think\Model;

/**
 * Class WithdrawOrder
 * @package app\common\model
 * @method getByTradeNo($trade_no)
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
class WithdrawOrder extends Model
{
    protected $name = 'withdraw_order';
    protected $autoWriteTimestamp = 'init';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $append = ['create_time_text', 'update_time_text', 'completion_time_text', 'status_text'];
    protected $insert = ['status' => 0];


    public function getStatusList()
    {
        return ['0' => __('Wait'), '1' => __('Successful'), '-1' => __('Fail')];
    }

    protected static function init()
    {
        self::beforeUpdate(static function ($row) {
            if ($row->status === 1) {
                $row->completion_time = time();
            }
        });
    }

    /**
     * @param $uid
     * @param $amount
     * @param string|array $card
     * @return $this
     * @throws Exception
     * @throws DbException
     */
    public function createOrder($uid, $amount, $card = '')
    {
        $site_config = \think\Config::get('site');
        $withdraw_rate = $site_config['withdraw_rate'];
        $fee = 0;
        if ($withdraw_rate) {
            $rateStr = 0;
            ksort($withdraw_rate);
            foreach ($withdraw_rate as $key => $item) {
                if ($amount > $key) {
                    $rateStr = $item;
                }
            }
            $fee = (float)$rateStr;
            $ratio = substr($rateStr, -1);
            if ($ratio === "%") {
                $fee = bcmul($amount, $fee / 100, 2);
            }
        }
        if (is_numeric($card)) {
            $cardData = UserBank::get($card);
            if ($cardData) {
                $card = $cardData->toArray();
            }
        }
        $order = [
            'user_id'         => $uid,
            'admin_id'        => '',
            'trade_no'        => '21' . time() . Random::numeric(4),
            'amount'          => $amount,
            'fee'             => $fee,
            'real_amount'     => bcsub($amount - $fee, 2),
            'channel_fee'     => 0,
            'card_data'       => json_encode($card),
            'merchant_config' => '',
            'result_data'     => ''
        ];
        $this->save($order);
        return $this;
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: $data['status'] ?? '';
        $list = $this->getStatusList();
        return !empty($list[$value]) ? $list[$value] : '';
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

    protected function setResultDataAttr($value)
    {
        return $value && is_array($value) ? json_encode($value) : $value;
    }

    protected function getResultDataAttr($value)
    {
        return $value ? json_decode($value, true) : $value;
    }

    protected function setCardDataAttr($value)
    {
        return $value && is_array($value) ? json_encode($value) : $value;
    }

    protected function getCardDataAttr($value)
    {
        return $value ? json_decode($value, true) : $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}