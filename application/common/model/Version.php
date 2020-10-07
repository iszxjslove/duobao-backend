<?php

namespace app\common\model;

use think\Model;

/**
 * Class Version
 * @package app/common/model
 * @property int id ID
 * @property string oldversion 旧版本号
 * @property string newversion 新版本号
 * @property string packagesize 包大小
 * @property string content 升级内容
 * @property string downloadurl 下载地址
 * @property int enforce 强制更新
 * @property int createtime 创建时间
 * @property int updatetime 更新时间
 * @property int weigh 权重
 * @property string status 状态
 */
class Version extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 定义字段类型
    protected $type = [
    ];

    /**
     * 检测版本号
     *
     * @param string $version 客户端版本号
     * @return array
     */
    public static function check($version)
    {
        $versionlist = self::where('status', 'normal')->cache('__version__')->order('weigh desc,id desc')->select();
        foreach ($versionlist as $k => $v) {
            // 版本正常且新版本号不等于验证的版本号且找到匹配的旧版本
            if ($v['status'] == 'normal' && $v['newversion'] !== $version && \fast\Version::check($version, $v['oldversion'])) {
                $updateversion = $v;
                break;
            }
        }
        if (isset($updateversion)) {
            $search = ['{version}', '{newversion}', '{downloadurl}', '{url}', '{packagesize}'];
            $replace = [$version, $updateversion['newversion'], $updateversion['downloadurl'], $updateversion['downloadurl'], $updateversion['packagesize']];
            $upgradetext = str_replace($search, $replace, $updateversion['content']);
            return [
                "enforce"     => $updateversion['enforce'],
                "version"     => $version,
                "newversion"  => $updateversion['newversion'],
                "downloadurl" => $updateversion['downloadurl'],
                "packagesize" => $updateversion['packagesize'],
                "upgradetext" => $upgradetext
            ];
        }
        return null;
    }
}
