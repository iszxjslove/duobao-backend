<?php


namespace app\common\model;


use think\Model;

/**
 * Class UserFinanceOrder
 * @package app/common/model
 * @property int id 金融订单ID
 * @property int user_id 用户ID
 * @property int financial_products_id 产品ID
 * @property string trade_no 订单号
 * @property string title 标题
 * @property string desc 描述
 * @property string type 类型
 * @property string period 周期
 * @property string period_unit 周期单位
 * @property float rate 利率
 * @property string interest_where 利息去向
 * @property string interest_settlement_time 结息时间
 * @property int next_period_time 最近一期计息时间
 * @property float contract_amount 合同金额
 * @property float remaining_amount 剩余合同金额
 * @property int end_time 结束时间
 * @property int create_time 创建时间
 * @property int update_time 更新时间
 * @property int status 状态 1 计息 2 结束
 */
class UserFinanceOrder extends Model
{
    protected $name = 'user_finance_order';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = 'update_time';

    protected $append = ['create_time_text', 'update_time_text'];

    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_time'] ?? '');
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['update_time'] ?? '');
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }
}