<?php

namespace app\common\model;

use fast\Random;
use Nested;
use PDOStatement;
use think\Collection;
use think\Config;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Model;

/**
 * 会员模型
 * Class User
 * @package app\common\model
 * @property int id ID
 * @property int pid 上级ID
 * @property string referrer 推荐码
 * @property int group_id 组别ID
 * @property string username 用户名
 * @property string nickname 昵称
 * @property string password 密码
 * @property string salt 密码盐
 * @property string payment_password 支付密码
 * @property string email 电子邮箱
 * @property string mobile 手机号
 * @property string avatar 头像
 * @property int level 等级
 * @property int gender 性别
 * @property string birthday 生日
 * @property string bio 格言
 * @property float money 余额
 * @property float hold_balance 冻结余额
 * @property float financial_money 余额宝
 * @property int score 积分
 * @property int successions 连续登录天数
 * @property int maxsuccessions 最大连续登录天数
 * @property int prevtime 上次登录时间
 * @property int logintime 登录时间
 * @property string loginip 登录IP
 * @property int loginfailure 失败次数
 * @property string joinip 加入IP
 * @property int jointime 加入时间
 * @property int createtime 创建时间
 * @property int updatetime 更新时间
 * @property string token Token
 * @property string status 状态
 * @property string verification 验证
 * @property int first_recharge 首充
 * @property int depth 深度
 * @property int lft 左范围
 * @property int rgt 右范围
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
     * @param bool $yuebao 余额不足是否扣余额宝
     * @throws DbException
     * @throws Exception
     */
    public static function payment($user_id, $money, $memo, $yuebao = true)
    {
        $user = self::get($user_id);
        if (!$user || $money <= 0) {
            throw new Exception('error 1002');
        }
        $after = 0;
        $change = $money;
        if ($user->money <= $money) {
            if (!$yuebao) {
                throw new Exception('Insufficient Balance');
            }
            $change = $user->money;
            $after = function_exists('bcsub') ? bcsub($money, $change, 2) : $money - $change;
        }
        self::money($user_id, -$change, $memo);
        if ($after > 0) {
            YuEBaoOrder::transferOut($user_id, $after, $memo);
        }
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
        if ($user && $money) {
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
     * @return bool|false|PDOStatement|string|Collection
     * @throws DbException
     * @throws Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public static function getParentsByUser(self $user)
    {
        $maxTeamLevel = Config::get('site.max_team_level');
        return (new Nested($user))->getParent($user->id, $maxTeamLevel - 1);
    }

    public function finance()
    {
        return $this->hasOne('UserFinance');
    }
}
