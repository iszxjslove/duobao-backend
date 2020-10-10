<?php


namespace app\common\model;


use think\Model;

/**
 * Class UserFinance
 * @package app\common\model
 * @property int id 金融账户ID
 * @property int user_id 用户ID
 * @property float balance 余额
 * @property float contract_amount 合同金额 弃
 * @property float interest 利息 弃
 * @property int create_time 开通时间
 * @property string status 状态 normal 正常 hold 冻结
 */
class UserFinance extends Model
{

    protected $name = 'user_finance';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = false;

    protected $append = ['create_time_text'];

    /**
     * @var bool 复利模式
     */
    private static $compoundInterest = false;

    /**
     * @var bool 利息转出（复利模式利息不转出）
     */
    private static $interestTransferredOut = true;

    public function getCreateTimeTextAttr($value, $data)
    {
        $value || $value = $data['create_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    /**
     * 开通金融账户
     * @param User $user
     * @return UserFinance
     */
    public static function opening(User $user)
    {
        $insertData = [
            'user_id'         => $user->id,
            'interest_rate'   => 0,
            'balance'         => 0,
            'contract_amount' => 0,
            'interest'        => 0
        ];
        return self::create($insertData);
    }

    public static function moneyByUserId($user_id, $money, $memo)
    {
        $user = User::get($user_id);
        if ($user) {
            self::money($user, $money, $memo);
        }
    }

    /**
     * 变更会员金融账户余额
     * @param User $user
     * @param int $money 金额
     * @param string $memo 备注
     */
    public static function money(User $user, $money, $memo)
    {
        $before = $user->finance->balance;
        $after = function_exists('bcadd') ? bcadd($before, $money, 2) : $before + $money;
        $user->finance->save(['balance' => $after]);
        //写入日志
        UserFinanceMoneyLog::create(['user_id' => $user->id, 'money' => $money, 'before' => $before, 'after' => $after, 'memo' => $memo]);
    }

    /**
     * 转入合同金额
     * @param User $user
     * @param $money
     * @param $memo
     */
    public static function contract(User $user, $money, $memo)
    {
        if ($user && $user->finance && $money > 0) {
            $user->finance->contract_amount = bcadd($user->finance->contract_amount, $money, 2);
            self::money($user, $money, $memo);
        }
    }

    /**
     * 增加利息
     * @param User $user
     * @param $money
     * @param $memo
     */
    public static function interestInc(User $user, $money, $memo)
    {
        if ($user && $money > 0) {
            if (self::$compoundInterest || !self::$interestTransferredOut) {
                // 复利模式或者指定利息不转出
                if ($user->finance) {
                    // 存入余额宝
                    $user->finance->interest = bcadd($user->finance->interest, $money, 2);
                    self::money($user, $money, $memo);
                }
            } else {
                // 利息转出到余额
                User::money($user, $money, $memo);
            }
        }
    }

    /**
     * 转出余额宝
     * @param User $user
     * @param $money
     * @param $memo
     */
    public static function withdrawal(User $user, $money, $memo)
    {
        if ($user && $user->finance && $money > 0) {
            // 先从利息扣除
            $user->finance->interest = bcsub($user->finance->interest, $money, 2);
            if ($user->finance->interest < 0) {
                // 利息不够扣本金
                $user->finance->contract_amount = bcsub($user->finance->contract_amount, abs($user->finance->interest), 2);
                $user->finance->interest = 0;
            }
            self::money($user, -$money, $memo);
        }
    }
}