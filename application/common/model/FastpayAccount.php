<?php


namespace app\common\model;


use think\Model;

class FastpayAccount extends Model
{
    protected $name = 'fastpay_account';

    public static function getUsable()
    {
        $list = self::where(['status' => 1])
            ->with(['fastpay'=>static function($query){
                $query->withField('en_name,ch_name');
            },'channel'=>static function($query){
                $query->withField('channel_value,channel_label,desc,pay_type,min_amount,max_amount');
            }])
            ->select();
        return $list;
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