<?php


namespace app\common\model;


use app\api\controller\Fastpay;
use think\Model;

/**
 * Class FastpayAccount
 * @package app/common/model
 * @property int id 
 * @property string fastpay 支付
 * @property string channel 通道
 * @property string amount_list 固定金额
 * @property string title 显示名称
 * @property int custom_amount 自定义金额
 * @property float min_amount 最小支付金额
 * @property float max_amount 最大支付金额
 * @property float fee_rate 手续费
 * @property string mch_id 商户ID
 * @property string app_id 应用ID
 * @property string private_secret 私钥
 * @property string public_secret 公钥
 * @property string version 支付版本
 * @property string desc 描述说明
 * @property int create_time 创建时间
 * @property int update_time 更新时间
 * @property int status 状态
 */
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