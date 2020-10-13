<?php

namespace app\admin\model\general;

use think\Model;




/**
 * Class Crontab
 * @package app/admin/model/general
 * @property int id 计划任务ID
 * @property string name 任务名称
 * @property string title 任务标题
 * @property string parameter 参数,以,分隔
 * @property int islocked 是否锁定:0正常，1锁定
 * @property int update_time 最后更新时间
 */
class Crontab extends Model
{

    

    

    // 表名
    protected $name = 'crontab';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'update_time_text'
    ];
    

    



    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['update_time']) ? $data['update_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
