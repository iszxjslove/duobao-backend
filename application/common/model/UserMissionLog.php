<?php


namespace app\common\model;


use think\Model;

class UserMissionLog extends Model
{
    protected $name = 'user_mission_log';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $append = [
        'create_time_text',
    ];

    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }
}