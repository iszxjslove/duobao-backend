<?php


namespace fastpay;


class Yaar
{
    protected $type = '';

    protected $env = 'dev';

    public function payin($orderInfo)
    {
        $this->type = 'payin';
        $merchant_config = $orderInfo['merchant_config'];
        $other_params = $orderInfo['other_params'];
        $params = [
            'amount'     => bcmul($orderInfo['amount'], 100),
            'appId'      => $merchant_config['app_id'],
            'channelId'  => $merchant_config['channel']['channel_value'],
            'currency'   => $other_params['currency'],
            'mchId'      => $merchant_config['mch_id'],
            'mchOrderNo' => $orderInfo['trade_no'],
            'notifyUrl'  => $this->getNotifyUrl(),
            'returnUrl'  => $this->getCallbackUrl(),
            'version'    => $other_params['version'],
        ];
        $extra = [];
        if (!empty($other_params['bank']['account_number'])) {
            $extra["depositAccount"] = $other_params['bank']['account_number'];
        }

        if (!empty($other_params['bank']['actual_name'])) {
            $extra["depositName"] = $other_params['bank']['actual_name'];
        }
        if (!empty($extra)) {
            $params['extra'] = json_encode($extra);
        }
        $params['sign'] = $this->makeSign($params, $merchant_config['private_secret']);
        return ['gateway' => $this->getPayUrl(), 'params' => $params];
    }

    public function payout($orderInfo)
    {
        $this->type = 'payout';
        $merchant_config = $orderInfo['merchant_config'];
        $params = [
            'accountName'    => $orderInfo['card_data']['actual_name'],
            'accountNo'      => $orderInfo['card_data']['account_number'],
            'amount'         => (int)$orderInfo['amount'],
            'mchId'          => $merchant_config['mch_id'],
            'mchOrderNo'     => $orderInfo['trade_no'],
            'notifyUrl'      => $this->getNotifyUrl(),
            'payoutBankCode' => $orderInfo['card_data']['bank_code'],
            'reqTime'        => time(),
            'ifscCode'       => $orderInfo['card_data']['ifsc_code']
        ];
        $params['sign'] = $this->makeSign($params, $merchant_config['private_secret']);
        return ['gateway' => $this->getPayUrl(), 'params' => $params];
    }

    public function getPayUrl()
    {
        $urls = [
            'payin'  => 'https://api.247yp.site/api/v1/payin/pay_info',
            'payout' => 'https://api.247yp.site/api/agentpay/apply'
        ];
        return $urls[$this->type];
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
            'amount'     => bcmul($params['amount'], 100),
            'appId'      => $merchant_config['appId'],
            'channelId'  => $params['channelId'],
            'currency'   => $params['currency'],
            'mchId'      => $merchant_config['mchId'],
            'mchOrderNo' => $params['trade_no'],
            'notifyUrl'  => $this->getNotifyUrl(),
            'returnUrl'  => $this->getCallbackUrl(),
            'version'    => $params['version'],
        ];

        $extra = [];
        if (!empty($params['depositAccount'])) {
            $extra["depositAccount"] = $params['depositAccount'];
        }

        if (!empty($params['depositName'])) {
            $extra["depositName"] = $params['depositName'];
        }
        if (!empty($extra)) {
            $data['extra'] = json_encode($extra);
        }
        $data['sign'] = $this->makeSign($data, $merchant_config['secret']);
        return $data;
    }

}