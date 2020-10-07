<?php


namespace app\common\model;


use think\Model;

/**
 * Class YuEBaoLog
 * @package app\common\model
 * @property int id 
 * @property int user_id 会员ID
 * @property float money 变更余额
 * @property float before 变更前余额
 * @property float after 变更后余额
 * @property string memo 备注
 * @property int create_time 创建时间
 */
class YuEBaoLog extends Model
{
    protected $name = 'yuebao_log';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = false;

    protected function getCreateTimeAttr($value)
    {
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value && is_string($value) ? strtotime($value) : $value;
    }
}