<?php

namespace app\admin\model;

use think\exception\DbException;
use think\Model;

/**
 * Class Admin
 * @package app/admin/model
 * @property int id ID
 * @property string username 用户名
 * @property string nickname 昵称
 * @property string password 密码
 * @property string salt 密码盐
 * @property float money 余额
 * @property string avatar 头像
 * @property string email 电子邮箱
 * @property int loginfailure 失败次数
 * @property int logintime 登录时间
 * @property string loginip 登录IP
 * @property int createtime 创建时间
 * @property int updatetime 更新时间
 * @property string token Session标识
 * @property string status 状态
 */
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
            $before = $admin->money ?: 0;
            //$after = $admin->money + $money;
            $after = function_exists('bcadd') ? bcadd($admin->money, $money, 2) : $admin->money + $money;
            //更新会员信息
            $admin->save(['money' => $after]);
            //写入日志
            AdminMoneyLog::create(['admin_id' => $admin_id, 'money' => $money, 'before' => $before, 'after' => $after, 'memo' => $memo]);
        }
    }
}
