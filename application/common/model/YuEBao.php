<?php


namespace app\common\model;


use think\Exception;
use think\exception\DbException;
use think\Model;

/**
 * Class YuEBao
 * @package app\common\model
 * @property int $user_id
 * @property float balance
 * @property float $sum_interest
 * @property int $update_time
 * @property int $create_time
 * @property string status
 */
class YuEBao extends Model
{
    /**
     * @var string
     */
    protected $name = 'yuebao';

    /**
     * @var string
     */
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = 'update_time';

    /**
     * @param $value
     * @return false|string
     */
    protected function getCreateTimeAttr($value)
    {
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value && is_string($value) ? strtotime($value) : $value;
    }

    protected function getUpdateTimeAttr($value)
    {
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value && is_string($value) ? strtotime($value) : $value;
    }

    /**
     * @param $user_id
     * @param $money
     * @param $memo
     * @throws DbException
     * @throws Exception
     */
    public static function balance($user_id, $money, $memo)
    {
        $account = self::get(['user_id' => $user_id]);
        if (!$account) {
            throw new Exception('Account does not exist');
        }
        if ($money) {
            $before = $account->balance;
            $after = function_exists('bcadd') ? bcadd($account->balance, $money, 2) : $account->balance + $money;
            //更新会员信息
            $account->save(['balance' => $after]);
            //写入日志
            YuEBaoLog::create(['user_id' => $account->id, 'money' => $money, 'before' => $before, 'after' => $after, 'memo' => $memo]);
        }
    }
}