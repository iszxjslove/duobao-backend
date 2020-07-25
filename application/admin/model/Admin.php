<?php

namespace app\admin\model;

use think\exception\DbException;
use think\Model;

class Admin extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 重置用户密码
     * @author baiyouwen
     */
    public function resetPassword($uid, $NewPassword)
    {
        $passwd = $this->encryptPassword($NewPassword);
        return $this->where(['id' => $uid])->update(['password' => $passwd]);
    }

    // 密码加密
    protected function encryptPassword($password, $salt = '', $encrypt = 'md5')
    {
        return $encrypt($password . $salt);
    }

    /**
     * 变更会员余额
     * @param int $money 金额
     * @param $admin_id
     * @param string $memo 备注
     * @throws DbException
     */
    public static function money($money, $admin_id, $memo)
    {
        $admin = self::get($admin_id);
        if ($admin && $money) {
            $before = $admin->money;
            //$after = $admin->money + $money;
            $after = function_exists('bcadd') ? bcadd($admin->money, $money, 2) : $admin->money + $money;
            //更新会员信息
            $admin->save(['money' => $after]);
            //写入日志
            AdminMoneyLog::create(['admin_id' => $admin_id, 'money' => $money, 'before' => $before, 'after' => $after, 'memo' => $memo]);
        }
    }
}
