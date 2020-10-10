<?php


namespace app\common\model;


use think\Model;



/**
 * Class Fastloan
 * @package app/common/model
 * @property int id 
 * @property string ch_name 中文名称
 * @property string en_name 英文名称
 * @property float fee_rate 手续费
 * @property string mch_id 商户ID
 * @property string app_id 应用ID
 * @property string private_secret 私钥
 * @property string public_secret 公钥
 * @property int status 状态
 */
class Fastloan extends Model
{
    protected $name = 'fastloan';
}