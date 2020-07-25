<?php


namespace app\api\model;


use app\common\model\User;
use think\Db;
use think\Exception;
use think\Model;

class FeeLog extends Model
{
    protected $name = 'user_fee_log';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = false;

    protected $append = [
        'create_time_text'
    ];

    protected function setCreateTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public static function feeInc($money, $user_id, $memo, $from_user_id = 0, $level = 0, $from_order_id = 0)
    {
        $insertData = [
            'user_id'       => $user_id,
            'money'         => $money,
            'level'         => $level,
            'from_user_id'  => $from_user_id,
            'from_order_id' => $from_order_id
        ];
        try {
            Db::startTrans();
            self::create($insertData);
            User::money($money, $user_id, $memo);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
        return true;
    }
}