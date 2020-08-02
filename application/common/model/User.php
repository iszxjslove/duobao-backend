<?php

namespace app\common\model;

use think\Config;
use think\Exception;
use think\exception\DbException;
use think\Model;

/**
 * 会员模型
 */
class User extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'url',
    ];

    public $nestedConfig = [
        'leftKey'    => 'lft',
        'rightKey'   => 'rgt',
        'levelKey'   => 'depth',
        'parentKey'  => 'pid',
        'primaryKey' => 'id',
    ];

    /**
     * 获取个人URL
     * @param string $value
     * @param array $data
     * @return string
     */
    public function getUrlAttr($value, $data)
    {
        return "/u/" . $data['id'];
    }

    /**
     * 获取头像
     * @param string $value
     * @param array $data
     * @return string
     */
    public function getAvatarAttr($value, $data)
    {
        if (!$value) {
            //如果不需要启用首字母头像，请使用
            //$value = '/assets/img/avatar.png';
            $value = letter_avatar($data['nickname']);
        }
        return $value;
    }

    /**
     * 获取会员的组别
     */
    public function getGroupAttr($value, $data)
    {
        return UserGroup::get($data['group_id']);
    }

    /**
     * 获取验证字段数组值
     * @param string $value
     * @param array $data
     * @return  object
     */
    public function getVerificationAttr($value, $data)
    {
        $value = array_filter((array)json_decode($value, true));
        $value = array_merge(['email' => 0, 'mobile' => 0], $value);
        return (object)$value;
    }

    /**
     * 设置验证字段
     * @param mixed $value
     * @return string
     */
    public function setVerificationAttr($value)
    {
        $value = is_object($value) || is_array($value) ? json_encode($value) : $value;
        return $value;
    }

    /**
     * 支付
     * @param $money
     * @param $user_id
     * @param $memo
     * @param bool $other 余额不足是否扣余额宝
     * @return bool
     * @throws DbException
     * @throws Exception
     */
    public static function payment($money, $user_id, $memo, $other = true)
    {
        $user = self::get($user_id);
        if (!$user || $money <= 0) {
            throw new Exception('system error');
        }
        if ($user->money < $money) {
            // 余额不足
            if ($other === true) {
                $diffMoney = $money - $user->money;
                if ($user->financial_money < $diffMoney) {
                    // 如果余额宝里的钱也不够
                    throw new Exception('Insufficient Balance');
                }
                if ($user->money > 0) {
                    self::money(-$user->money, $user_id, $memo);
                }
                self::financial_money(-$diffMoney, $user_id, $memo);
                return true;
            }
            throw new Exception('Insufficient Balance');
        }
        self::money(-$money, $user_id, $memo);
        return true;
    }

    /**
     * 变更会员余额
     * @param User $user
     * @param int $money 金额
     * @param string $memo 备注
     */
    public static function money(int $user_id, $money, $memo)
    {
        $user = self::get($user_id);
        if($user && $money){
            $before = $user->money;
            $after = function_exists('bcadd') ? bcadd($user->money, $money, 2) : $user->money + $money;
            //更新会员信息
            $user->save(['money' => $after]);
            //写入日志
            MoneyLog::create(['user_id' => $user->id, 'money' => $money, 'before' => $before, 'after' => $after, 'memo' => $memo]);
        }
    }

    /**
     * 变更会员冻结余额
     * @param int $money 金额
     * @param int $user_id 会员ID
     * @param string $memo 备注
     * @throws DbException
     */
    public static function hold_balance(int $user_id, $money, $memo)
    {
        $user = self::get($user_id);
        if ($user && $money) {
            $before = $user->hold_balance;
            $after = function_exists('bcadd') ? bcadd($user->hold_balance, $money, 2) : $user->hold_balance + $money;
            //更新会员信息
            $user->save(['hold_balance' => $after]);
            //写入日志
            HoldBalanceLog::create(['user_id' => $user->id, 'money' => $money, 'before' => $before, 'after' => $after, 'memo' => $memo]);
        }
    }

    /**
     * 变更会员余额宝余额
     * @param int $money 金额
     * @param int $user_id 会员ID
     * @param string $memo 备注
     * @throws DbException
     */
    public static function financial_money($money, $user_id, $memo)
    {
        $user = self::get($user_id);
        if ($user && $money != 0) {
            $before = $user->financial_money;
            //$after = $user->money + $money;
            $after = function_exists('bcadd') ? bcadd($user->financial_money, $money, 2) : $user->financial_money + $money;
            //更新会员信息
            $user->save(['financial_money' => $after]);
            //写入日志
            FinancialMoneyLog::create(['user_id' => $user_id, 'money' => $money, 'before' => $before, 'after' => $after, 'memo' => $memo]);
        }
    }

    /**
     * 变更会员积分
     * @param int $score 积分
     * @param int $user_id 会员ID
     * @param string $memo 备注
     */
    public static function score($score, $user_id, $memo)
    {
        $user = self::get($user_id);
        if ($user && $score != 0) {
            $before = $user->score;
            $after = $user->score + $score;
            $level = self::nextlevel($after);
            //更新会员信息
            $user->save(['score' => $after, 'level' => $level]);
            //写入日志
            ScoreLog::create(['user_id' => $user_id, 'score' => $score, 'before' => $before, 'after' => $after, 'memo' => $memo]);
        }
    }

    /**
     * 根据积分获取等级
     * @param int $score 积分
     * @return int
     */
    public static function nextlevel($score = 0)
    {
        $lv = array(1 => 0, 2 => 30, 3 => 100, 4 => 500, 5 => 1000, 6 => 2000, 7 => 3000, 8 => 5000, 9 => 8000, 10 => 10000);
        $level = 1;
        foreach ($lv as $key => $value) {
            if ($score >= $value) {
                $level = $key;
            }
        }
        return $level;
    }

    /**
     * 获取所有上级
     * @param User $user
     * @return bool|false|\PDOStatement|string|\think\Collection
     * @throws DbException
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getParentsByUser(self $user)
    {
        $maxTeamLevel = Config::get('site.max_team_level');
        $parents = (new \Nested($user))->getParent($user->id, $maxTeamLevel - 1);
        return $parents;
    }

    public function finance()
    {
        return $this->hasOne('UserFinance');
    }
}
