<?php

namespace app\api\model\finance;

use think\Model;


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
