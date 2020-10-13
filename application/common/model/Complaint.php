<?php


namespace app\common\model;


use think\Model;

/**
 * Class Complaint
 * @package app/common/model
 * @property int id 工单ID
 * @property int user_id 用户ID
 * @property int category_id 分类ID
 * @property string desc 内容
 * @property string whatsapp 联系方法
 * @property int create_time 创建时间
 * @property int status 状态 0 未处理 1 已处理
 */
class Complaint extends Model
{
    protected $name = 'complaint';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = false;

    protected $append = [
        'create_time_text'
    ];

    protected $insert = ['status'=>0];



    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }
}