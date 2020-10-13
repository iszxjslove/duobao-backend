<?php

namespace app\admin\model\user;

use think\Model;




/**
 * Class Fee
 * @package app/admin/model/user
 * @property int id 佣金ID
 * @property int user_id 用户ID
 * @property float money 佣金
 * @property int from_user_id 来源用户ID
 * @property int level 层级
 * @property int from_order_id 来源订单ID
 * @property int create_time 创建时间
 * @property string memo 备注
 * @property int update_time 更新时间
 * @property int receive_time 领取时间
 * @property int apply_id 提取记录
 * @property int status 状态
 */
class Fee extends Model
{

    

    

    // 表名
    protected $name = 'user_fee_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text',
        'update_time_text',
        'receive_time_text'
    ];
    

    



    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['update_time']) ? $data['update_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getReceiveTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['receive_time']) ? $data['receive_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setReceiveTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
