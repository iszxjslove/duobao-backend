<?php

namespace app\common\model;

use think\Model;

/**
 * 短信验证码
 * @property int id ID
 * @property string event 事件
 * @property string mobile 手机号
 * @property string code 验证码
 * @property int times 验证次数
 * @property string ip IP
 * @property int createtime 创建时间
 */
class Sms Extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

}
