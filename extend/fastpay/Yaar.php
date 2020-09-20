<?php


namespace fastpay;


class Yaar extends Base
{
    public function getPayUrl()
    {
//        https://api.247yp.site/api/v1/payin/pay_info
//        https://api.247yp.site/api/v1/payin/pay_info
//        https://pre-prod.api.247yp.site/api/v1/payin/pay_info
//        https://pre-prod.api.247yp.site/api/v1/payin/pay_info

        return 'https://pre-prod.api.247yp.site/api/v1/payin/pay_info';
    }

    public function getCallbackUrl($domain = '')
    {
        return 'http://www.winwinclubs.com/#/page/recharge';
    }

    public function getNotifyUrl($domain = '')
    {
        return url('fastpay/yaar/notify');
    }

    public function checkSign($params, $sign, $secret)
    {
        // TODO: Implement checkSign() method.
    }

    public function makeSign($params, $secret)
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
        ksort($params);
        $string = '';
        foreach ($params as $key => $value) {
            if ($value === '' || $key === 'sign') {
                continue;
            }
            $string .= trim($key) . "=" . trim($value) . '&';
        }
        return rtrim($string, '&');
    }

    public function buildParams($params)
    {
        $merchant_config = json_decode($params['merchant_config'], true);
        $data = [
            'amount'          => bcmul($params['amount'], 100),
            'appId'           => $merchant_config['appId'],
            'channelId'       => $params['channelId'],
            'currency'        => $params['currency'],
            'mchId'           => $merchant_config['mchId'],
            'mchOrderNo'      => $params['trade_no'],
            'notifyUrl'       => $this->getNotifyUrl(),
            'returnUrl'       => $this->getCallbackUrl(),
            'version'         => $params['version'],
        ];

        $extra = [];
        if (!empty($params['depositAccount'])) {
            $extra["depositAccount"] = $params['depositAccount'];
        }

        if (!empty($params['depositName'])) {
            $extra["depositName"] = $params['depositName'];
        }
        if(!empty($extra)){
            $data['extra'] = json_encode($extra);
        }
        $data['sign'] = $this->makeSign($data, $merchant_config['secret']);
        return $data;
    }

}