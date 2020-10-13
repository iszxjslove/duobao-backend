<?php

namespace app\admin\model\withdraw;

use app\admin\model\Admin;
use app\admin\model\User;
use think\Model;

/**
 * Class Order
 * @package app/admin/model/withdraw
 * @property int id 提现记录ID
 * @property int user_id 用户ID
 * @property int admin_id 管理员ID
 * @property string trade_no 交易号
 * @property float amount 金额
 * @property float fee 手续费
 * @property float real_amount 实付金额
 * @property float channel_fee 通道手续费
 * @property string card_data 银行卡数据
 * @property string merchant_config 商户参数
 * @property string result_data 结果数据
 * @property int create_time 创建时间
 * @property int update_time 更新时间
 * @property int completion_time 完成时间
 * @property int status 状态
 */
class Order extends Model
{


    // 表名
    protected $name = 'withdraw_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text',
        'update_time_text',
        'status_text'
    ];


    public function getStatusList()
    {
        return ['wait' => __('Wait'), 'successful' => __('Successful'), 'fail' => __('Fail')];
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['update_time']) ? $data['update_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
