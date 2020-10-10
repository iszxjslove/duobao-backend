<?php

namespace app\admin\model;

use think\Cache;
use think\Model;



/**
 * Class AuthRule
 * @package app/admin/model
 * @property int id 
 * @property string type menu为菜单,file为权限节点
 * @property int pid 父ID
 * @property string name 规则名称
 * @property string title 规则名称
 * @property string icon 图标
 * @property string condition 条件
 * @property string remark 备注
 * @property int ismenu 是否为菜单
 * @property int createtime 创建时间
 * @property int updatetime 更新时间
 * @property int weigh 权重
 * @property string status 状态
 */
class AuthRule extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    protected static function init()
    {
        self::afterWrite(function ($row) {
            Cache::rm('__menu__');
        });
    }

    public function getTitleAttr($value, $data)
    {
        return __($value);
    }

}
