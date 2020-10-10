<?php

namespace app\admin\model;

use think\Model;




/**
 * Class Sms
 * @package app/admin/model
 * @property int id ID
 * @property string event 事件
 * @property string mobile 手机号
 * @property string code 验证码
 * @property int times 验证次数
 * @property string ip IP
 * @property int createtime 创建时间
 */
class Sms extends Model
{

    

    

    // 表名
    protected $name = 'sms';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







}
