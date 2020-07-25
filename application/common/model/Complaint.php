<?php


namespace app\common\model;


use think\Model;

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