<?php

namespace app\admin\model;

use app\common\model\Base;
use think\Model;




/**
 * Class Mission
 * @package app/admin/model
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


    // 表名
    protected $name = 'mission';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text',
        'release_time_text',
        'start_time_text',
        'end_time_text',
    ];

    protected static function init()
    {
        self::beforeInsert(static function ($row) {
            $row->still_some = $row->amount_limit;
        });
    }

    public function getStatusList()
    {
        return ['0' => '未发布', '2' => '已下架', '1' => '已发布'];
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getReleaseTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['release_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStartTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['start_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getEndTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['end_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setReleaseTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setStartTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setEndTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


    public function admin()
    {
        return $this->belongsTo('Admin', 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
