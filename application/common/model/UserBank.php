<?php


namespace app\common\model;


use think\Model;

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