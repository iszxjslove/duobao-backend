<?php


namespace app\common\model;


use think\Model;

class UserFinanceMoneyLog extends Model
{
    protected $name = 'user_finance_money_log';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = false;

    protected $append = ['create_time_text'];

    public function getCreateTimeTextAttr($value, $data)
    {
        $value || $value = $data['create_time'];
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }
}