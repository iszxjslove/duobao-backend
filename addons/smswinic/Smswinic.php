<?php

namespace addons\smswinic;

use think\Addons;
use think\Exception;
use think\Validate;

/**
 * 插件
 */
class Smswinic extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {

        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {

        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {

        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {

        return true;
    }

    /**
     * 短信发送
     */
    public function smsSend($params)
    {
        $smswinic = new library\Smswinic();
        return $smswinic->mobile($params['mobile'])->msg("What is your SMS verification code：{$params['code']}")->send();
    }

    /**
     * 短信发送通知（msg参数直接构建实际短信内容即可）
     * @param $params
     * @return bool|mixed
     */
    public function smsNotice($params)
    {
        $smswinic = new library\Smswinic();
        return $smswinic->mobile($params['mobile'])->msg($params['msg'])->send();
    }

    public function checkMobile($mobile)
    {
        return $mobile;
    }

    /**
     * 检测验证是否正确
     * @param $params
     * @return bool
     */
    public function smsCheck($params)
    {
        return TRUE;
    }

}
