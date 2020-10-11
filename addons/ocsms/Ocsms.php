<?php

namespace addons\ocsms;

use app\common\library\Menu;
use think\Addons;

/**
 * 插件
 */
class Ocsms extends Addons
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
        $smswinic = new library\Ocsms();
        return $smswinic->mobile($params['mobile'])->msg("your Verification code is {$params['code']}")->send();
    }

    /**
     * 短信发送通知（msg参数直接构建实际短信内容即可）
     * @param $params
     * @return bool|mixed
     */
    public function smsNotice($params)
    {
        $smswinic = new library\Ocsms();
        return $smswinic->mobile($params['mobile'])->msg($params['msg'])->send();
    }

    public function checkMobile($mobile)
    {
        return TRUE;
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
