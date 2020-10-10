<?php

namespace app\common\model;

use think\Model;



/**
 * Class Attachment
 * @package app/common/model
 * @property int id ID
 * @property int admin_id 管理员ID
 * @property int user_id 会员ID
 * @property string url 物理路径
 * @property string imagewidth 宽度
 * @property string imageheight 高度
 * @property string imagetype 图片类型
 * @property int imageframes 图片帧数
 * @property int filesize 文件大小
 * @property string mimetype mime类型
 * @property string extparam 透传数据
 * @property int createtime 创建日期
 * @property int updatetime 更新时间
 * @property int uploadtime 上传时间
 * @property string storage 存储位置
 * @property string sha1 文件 sha1编码
 */
class Attachment extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 定义字段类型
    protected $type = [
    ];

    public function setUploadtimeAttr($value)
    {
        return is_numeric($value) ? $value : strtotime($value);
    }

    public static function getMimetypeList()
    {
        $data = [
            "image/*"        => "图片",
            "audio/*"        => "音频",
            "video/*"        => "视频",
            "text/*"         => "文档",
            "application/*"  => "应用",
            "zip,rar,7z,tar" => "压缩包",
        ];
        return $data;
    }

    protected static function init()
    {
        // 如果已经上传该资源，则不再记录
        self::beforeInsert(function ($model) {
            if (self::where('url', '=', $model['url'])->find()) {
                return false;
            }
        });
    }
}
