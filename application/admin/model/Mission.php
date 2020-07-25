<?php

namespace app\admin\model;

use think\Model;


class Mission extends Model
{


    // 表名
    protected $name = 'mission';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text'
    ];

    protected static function init()
    {
        self::beforeInsert(static function($row){
           $row->still_some = $row->amount_limit;
        });
    }

    public function getStatusList()
    {
        return ['0' => '未发布', '2' => '已下架', '1' => '已发布'];
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function admin()
    {
        return $this->belongsTo('Admin', 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
