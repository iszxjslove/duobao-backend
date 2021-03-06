<?php


namespace app\api\model;


use app\common\model\User;
use think\Db;
use think\Exception;
use think\Model;

/**
 * Class FeeLog
 * @package app/api/model
 * @property int id 佣金ID
 * @property int user_id 用户ID
 * @property float money 佣金
 * @property int from_user_id 来源用户ID
 * @property int level 层级
 * @property int from_order_id 来源订单ID
 * @property int create_time 创建时间
 * @property string memo 备注
 * @property int update_time 更新时间
 * @property int receive_time 领取时间
 * @property int apply_id 提取记录
 * @property int status 状态
 */
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
            'from_order_id' => $from_order_id,
            'memo'          => $memo,
            'status'        => 0
        ];
        return self::create($insertData);
    }
}