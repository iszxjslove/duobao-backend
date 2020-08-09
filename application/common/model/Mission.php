<?php


namespace app\common\model;


use think\exception\DbException;
use think\Model;

class Mission extends Base
{
    protected $name = 'mission';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = false;

    protected $append = [
        'create_time_text',
    ];


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: $data['create_time'] ?? '';
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function usermission()
    {
        return $this->hasMany('UserMission');
    }

    /**
     * 获取可领取的任务
     * @param $mission_name
     * @return Mission|array|false
     * @throws DbException
     */
    public static function getMission($mission_name)
    {
        $statusValue = (new self)->getCurrentTableFieldConfig("status.up.value");
        $missions = self::all(['mission_name' => 'first_login', 'status' => $statusValue]);
        if (!$missions) {
            return [];
        }
        $now = time();
        $list = [];
        foreach ($missions as $mission) {
            // 可领取的时间内
            if (strtotime($mission->start_time) <= $now && strtotime($mission->end_time) > $now) {
                // 还有余量
                if(!$mission->amount_limit || $mission->received_amount > 0){
                    $list[] = $mission;
                }
            }
        }
        return $list;
    }

}