<?php


namespace app\common\model;


use think\Model;

/**
 * Class UserBank
 * @package app/common/model
 * @property int id 银行卡ID
 * @property int user_id 用户ID
 * @property string actual_name 实际名称
 * @property string ifsc_code IFSC代码
 * @property string account_number 银行帐号
 * @property string state 国家
 * @property string bank_code 
 * @property string city 城市
 * @property string address 地址
 * @property string mobile_number 手机号码
 * @property string email 邮箱
 * @property int is_default 是否默认
 * @property int create_time 创建时间
 */
class UserBank extends Model
{
    protected $name = 'user_bank';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = false;

    protected $append = [
        'create_time_text'
    ];

    public function getCreateTimeTextAttr($value, $data)
    {
        return $data['create_time'] && is_numeric($data['create_time']) ? date('Y-m-d H:i:s', $data['create_time']) : $data['create_time'];
    }
}