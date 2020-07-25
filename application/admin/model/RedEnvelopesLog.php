<?php


namespace app\admin\model;


use think\Model;

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