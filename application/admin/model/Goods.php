<?php

namespace app\admin\model;

use think\Model;




/**
 * Class Goods
 * @package app/admin/model
 * @property int id 商品ID
 * @property string picture 图片
 * @property string title 标题
 * @property float selling_price 销售价格
 * @property float market_price 市场价格
 * @property int stocks 库存
 * @property string desc 详情
 * @property string status 状态
 */
class Goods extends Model
{

    

    

    // 表名
    protected $name = 'goods';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    
    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
