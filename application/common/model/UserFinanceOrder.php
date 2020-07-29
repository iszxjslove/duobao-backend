<?php


namespace app\common\model;


use think\Model;

class UserFinanceOrder extends Model
{
    protected $name = 'user_finance_order';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = 'update_time';

    protected $append = ['create_time_text', 'update_time_text'];

    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_time'] ?? '');
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['update_time'] ?? '');
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }
}