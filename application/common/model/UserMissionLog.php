<?php


namespace app\common\model;


use think\Model;

/**
 * Class UserMissionLog
 * @package app/common/model
 * @property int id 任务记录ID
 * @property int user_id 用户ID
 * @property int user_mission_id 用户任务ID
 * @property string content 记录内容
 * @property float amount 统计量
 * @property int create_time 创建时间
 */
class UserMissionLog extends Model
{
    protected $name = 'user_mission_log';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = false;

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