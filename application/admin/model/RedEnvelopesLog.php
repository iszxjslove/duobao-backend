<?php


namespace app\admin\model;


use think\Model;



/**
 * Class RedEnvelopesLog
 * @package app/admin/model
 * @property int id 领取记录ID
 * @property int user_id 用户ID
 * @property string cate 红包类型
 * @property int red_edvelopes_id 红包ID
 * @property int form_admin_id 管理员ID
 * @property float get_amount 领取金额
 * @property int create_time 领取时间
 */
class RedEnvelopesLog extends Model
{
    protected $name = 'red_envelopes_log';

    protected $append = [
      'create_time_text'
    ];


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}