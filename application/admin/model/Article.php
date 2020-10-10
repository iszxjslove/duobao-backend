<?php

namespace app\admin\model;

use app\common\library\Str;
use think\Log;
use think\Model;




/**
 * Class Article
 * @package app/admin/model
 * @property int id 文章ID
 * @property int category_id 文章分类
 * @property string name 变量名称
 * @property string type 文章类型
 * @property string title 文章标题
 * @property string subtitle 副标题
 * @property string thumbnail 缩略图
 * @property string content 内容
 * @property int create_time 创建时间
 * @property int update_time 更新时间
 * @property int need_login 1 需要登录 0 不需要
 * @property string status 状态
 */
class Article extends Model
{


    // 表名
    protected $name = 'article';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'create_time_text',
        'update_time_text',
        'status_text'
    ];


    public function getTypeList()
    {
        return ['common' => __('Common'), 'system' => __('System')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['update_time']) ? $data['update_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setDescriptionAttr($value, $data)
    {
        $value = $value ? $value : mb_strcut(Str::filterHtml($data['content']), 0, 500);
        return $value;
    }

    public function category()
    {
        return $this->belongsTo('app\common\model\Category', 'category_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
