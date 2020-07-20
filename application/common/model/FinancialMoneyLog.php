<?php


namespace app\common\model;


use think\Model;

class FinancialMoneyLog extends Model
{
    // 表名
    protected $name = 'user_financial_money_log';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
    ];
}