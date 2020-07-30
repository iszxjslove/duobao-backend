<?php


namespace fastpay;


use think\Request;

/**
 * 快捷支付基类
 * Class Base
 * @package fastpay
 */
abstract class Base
{
    /**
     * @return string
     */
    abstract public function getPayUrl();

    /**
     * @param string $domain
     * @return string
     */
    abstract public function getCallbackUrl($domain = '');

    /**
     * @param string $domain
     * @return string
     */
    abstract public function getNotifyUrl($domain = '');

    /**
     * @param array $params
     * @param string $sign
     * @param string $secret
     * @return boolean
     */
    abstract public function checkSign($params, $sign, $secret);

    /**
     * @param array $params
     * @param string $secret
     * @return string
     */
    abstract public function makeSign($params, $secret);

    /**
     * @param array $params
     * @return array
     */
    abstract public function buildParams($params);
}