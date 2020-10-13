<?php

namespace app\common\model;

use app\api\model\FeeLog;
use think\Model;

/**
 * Class TeamBonusApply
 * @package app/common/model
 * @property int id 
 * @property int user_id 用户
 * @property float amount 金额
 * @property int create_time 申请时间
 * @property int update_time 更新时间
 * @property int admin_id 管理员
 * @property int check_time 审核时间
 * @property int status 状态
 */
class TeamBonusApply extends Model
{

    

    

    // 表名
    protected $name = 'team_bonus_apply';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text',
        'update_time_text',
    ];






    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }




    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['update_time']) ? $data['update_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function records()
    {
        return $this->hasMany(FeeLog::class, 'apply_id', 'id');
    }

}
