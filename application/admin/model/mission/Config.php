<?php

namespace app\admin\model\mission;

use think\Model;


class Config extends Model
{

    

    

    // 表名
    protected $name = 'mission_config';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'standard_conditions_text',
        'method_text',
        'status_text'
    ];
    

    
    public function getStandardConditionsList()
    {
        return ['times' => __('Times'), 'total' => __('Total')];
    }

    public function getMethodList()
    {
        return ['private' => __('Private'), 'parent' => __('Parent')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }


    public function getStandardConditionsTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['standard_conditions']) ? $data['standard_conditions'] : '');
        $valueArr = explode(',', $value);
        $list = $this->getStandardConditionsList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }


    public function getMethodTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['method']) ? $data['method'] : '');
        $list = $this->getMethodList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setStandardConditionsAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }


}
