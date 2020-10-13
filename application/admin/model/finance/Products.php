<?php

namespace app\admin\model\finance;

use think\Model;




/**
 * Class Products
 * @package app/admin/model/finance
 * @property int id 金融产品ID
 * @property string type 类型
 * @property string title 标题
 * @property string desc 描述
 * @property int period 周期
 * @property string period_unit 周期单位
 * @property float rate 利率
 * @property string interest_where 利息去向
 * @property string interest_settlement_time 结息时间
 * @property string status 状态
 */
class Products extends Model
{


    // 表名
    protected $name = 'financial_products';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'period_unit_text',
        'type_text',
        'status_text'
    ];

    protected static function init()
    {
        self::beforeWrite(static function ($row) {
            if ($row['type'] === 'current') {
                $row->period = 1;
                $row->period_unit = 'day';
            }
        });
    }


    public function getPeriodUnitList()
    {
        return ['day' => __('Day'), 'week' => __('Week'), 'month' => __('Month'), 'year' => __('Year')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getTypeList()
    {
        return ['regular' => __('Regular'), 'current' => __('Current')];
    }


    public function getPeriodUnitTextAttr($value, $data)
    {
        $value = $value ?: ($data['period_unit'] ?? '');
        $list = $this->getPeriodUnitList();
        return $list[$value] ?? '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }


    public function getRateAttr($value)
    {
        return bcmul($value, 100, 4);
    }

    public function setRateAttr($value)
    {
        return bcdiv($value, 100, 4);
    }
}
