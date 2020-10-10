<?php


namespace app\common\model;


use think\Model;



/**
 * Class Fastpay
 * @package app/common/model
 * @property int id 
 * @property string ch_name 支付名称
 * @property string en_name 英文名称
 */
class Fastpay extends Model
{
    protected $name = 'fastpay';
}