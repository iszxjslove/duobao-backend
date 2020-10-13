<?php


namespace app\common\model;


use think\exception\DbException;
use think\Model;

/**
 * Class Mission
 * @package app/common/model
 * @property int id 任务ID
 * @property int admin_id 管理员ID
 * @property int mission_config_id 任务配置ID
 * @property string group_name 任务分组
 * @property string mission_name 任务名称
 * @property string method 统计方式
 * @property int level 层级
 * @property string title 标题
 * @property string desc 任务描述
 * @property int times 次数统计
 * @property int total 累加合计
 * @property string total_field 合计字段
 * @property int cycle 任务周期
 * @property string cycle_unit 周期计算单位
 * @property int cycle_time 计数周期时长
 * @property int amount_limit 参与人数限制
 * @property int surplus_amount 剩余量
 * @property int received_amount 领取量
 * @property float bonus 任务奖励
 * @property int create_time 创建时间
 * @property int release_time 发布时间
 * @property int start_time 开始时间
 * @property int end_time 结束时间
 * @property int status 状态 0 未发布 1 已发布 （未开始，已开始，已结束） 2 已下架
 */
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