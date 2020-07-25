<?php


namespace app\api\model;


use think\Model;

class RedEnvelopesLog extends Model
{
    protected $name = 'red_envelopes_log';

    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text',
    ];

    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function users()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->field('id,username,nickname')->bind([
            'name'=>'username','nickname'=>'nickname'
        ]);
    }

}