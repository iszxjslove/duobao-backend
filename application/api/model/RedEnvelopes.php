<?php


namespace app\api\model;


use app\common\model\User;
use app\common\model\UserStatistics;
use think\Db;
use think\Exception;
use think\Model;



/**
 * Class RedEnvelopes
 * @package app/api/model
 * @property int id 红包ID
 * @property int admin_id 管理员ID
 * @property string cate 红包类型
 * @property float amount 红包金额
 * @property int number 红包数量
 * @property int remaining_number 剩余数量
 * @property float total_amount 总金额
 * @property float remaining_amount 剩余金额
 * @property string title 标题
 * @property string cover 封面
 * @property int create_time 创建时间
 * @property string last_time 最后一个领取时间
 * @property int expiry_time 过期时间
 * @property int claim_status 领取状态 0 未领取 1 领取中 2 已领完
 * @property int return_status 退还状态 0 未退还 1 已退还
 * @property string token 红包密码
 * @property string code 红包码
 */
class RedEnvelopes extends Model
{
    // 表名
    protected $name = 'red_envelopes';

    public function admin()
    {
        return $this->belongsTo('Admin')->bind(['form_user' => 'nickname']);
    }

    public static function openRed(self $redEnvelopes, $user_id)
    {
        if ($redEnvelopes->cate === 'fixed') {
            $get = $redEnvelopes->amount;
        } else {
            if ($redEnvelopes->remaining_number > 1) {
                // 剩余转整数
                $remaining_amount = (int)($redEnvelopes->remaining_amount * 100);
                $ave = $remaining_amount / $redEnvelopes->remaining_number;
                # 保留,以免抢完
                $retain = 0;
                for ($i = 1; $i < $redEnvelopes->remaining_number; $i++) {
                    $retain += (int)random_int(1, (int)$ave);
                }
                $get = random_int(1, $remaining_amount - $retain) / 100;
            } else {
                $get = $redEnvelopes->remaining_amount;
            }
        }

        try {
            Db::startTrans();
            $redEnvelopes->remaining_number -= 1;
            $redEnvelopes->remaining_amount -= $get;
            $redEnvelopes->claim_status = 1;
            $redEnvelopes->last_time = date('Y-m-d H:i:s');
            if ($redEnvelopes->remaining_number < 1) {
                $redEnvelopes->claim_status = 2;
            }
            $redEnvelopes->save();
            $insertData = [
                'user_id'          => $user_id,
                'red_edvelopes_id' => $redEnvelopes->id,
                'form_admin_id'    => $redEnvelopes->admin_id,
                'get_amount'       => $get,
                'cate'             => $redEnvelopes->cate
            ];
            RedEnvelopesLog::create($insertData);
            User::money($user_id, $get, $redEnvelopes->cate . ' red envelope');
            UserStatistics::push('red_envelopes', $insertData['get_amount'], 'cash_gift');
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage());
        }
        return $get;
    }

    public function logs()
    {
        return $this->hasMany('RedEnvelopesLog', 'red_edvelopes_id', 'id');
    }
}