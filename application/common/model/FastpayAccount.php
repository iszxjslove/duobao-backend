<?php


namespace app\common\model;


use app\api\controller\Fastpay;
use think\Model;

class FastpayAccount extends Model
{
    protected $name = 'fastpay_account';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = 'update_time';

    protected $append = ['fastpay_config','channel_type'];

    protected function getFastpayConfigAttr($value, $data)
    {
        $list = $this->getFastpay();
        return !empty($data['fastpay']) ? $list[$data['fastpay']] : '';
    }

    protected function getChannelTypeAttr($value, $data)
    {
        $list = $this->getFastpay('payin');
        $channels = !empty($data['fastpay']) ? $list[$data['fastpay']] : '';
        return !empty($data['channel']) ? $channels['payin']['channel'][$data['channel']]['type'] : '';
    }

    public function getFastpay($type = ''): array
    {
        return Fastpay::selectFastpay($type);
    }

    public function channel()
    {
        return $this->belongsTo(FastpayChannel::class, 'channel_id', 'id', '', 'LEFT')->setEagerlyType(0);
    }

    public function fastpay()
    {
        return $this->belongsTo(Fastpay::class, 'fastpay_id', 'id', '', 'LEFT')->setEagerlyType(0);
    }
}