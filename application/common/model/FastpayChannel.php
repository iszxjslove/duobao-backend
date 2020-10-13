<?php


namespace app\common\model;


use think\Model;

/**
 * Class FastpayChannel
 * @package app/common/model
 * @property int id 
 * @property int fastpay_id 支付ID
 * @property string channel_label 通道标题
 * @property string channel_value 通道值
 * @property string desc 描述
 * @property string amount_list 付款金额
 * @property string pay_type 支付方式
 * @property float min_amount 最小金额
 * @property float max_amount 最大金额
 */
class FastpayChannel extends Model
{
    protected $name = 'fastpay_channel';
}