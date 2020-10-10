<?php

namespace app\admin\model;

use think\Model;




/**
 * Class FastpayAccount
 * @package app/admin/model
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

    

    

    // 表名
    protected $name = 'fastpay_account';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







}
