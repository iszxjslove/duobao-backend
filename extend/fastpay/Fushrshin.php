<?php


namespace fastpay;


class Fushrshin
{
    protected $type = '';

    protected $env = 'dev';

    public function payin($orderInfo)
    {
        $this->type = 'payin';
        $merchant_config = $orderInfo['merchant_config'];
        $params = [
            'appid'        => $merchant_config['app_id'],
            'pay_type'     => $merchant_config['channel']['channel_value'],
            'out_trade_no' => $orderInfo['trade_no'],
            'amount'       => $orderInfo['amount'],
            'callback_url' => $this->getCallbackUrl(),
            'success_url'  => $this->getCallbackUrl(),
            'error_url'    => $this->getCallbackUrl(),
            'version'      => $merchant_config['version'],
            'out_uid'      => $orderInfo['user_id']
        ];

        $params['sign'] = $this->makeSign($params, $merchant_config['private_secret']);
        return ['gateway' => $this->getPayUrl(), 'params' => $params];
    }

    public function makeSign($params, $secret): string
    {
        return strtoupper(md5(self::parseSignStr($params) . '&key=' . $secret));
    }

    /**
     * 解析签名字符串
     * @param $params
     * @return string
     */
    public static function parseSignStr($params): string
    {
        $params = array_filter($params);
        ksort($params);
        $signString = http_build_query($params);
        $signString = urldecode($signString);
        return $signString;
    }

    public function getPayUrl()
    {
        return 'http://api.hocan.cn/index/unifiedorder';
    }

    public function getCallbackUrl()
    {
        return url('fushrshin/index');
    }
}