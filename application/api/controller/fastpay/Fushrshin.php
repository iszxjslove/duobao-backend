<?php


namespace app\api\controller\fastpay;


use app\api\controller\Fastpay;

class Fushrshin extends Fastpay
{
    public static function getInfo(): array
    {
        return [
            'name'    => 'fushrshin',
            'label'   => '富士昕',
            'gateway' => [
                'unified' => 'http://api.hocan.cn/index/unifiedorder'
            ],
            'payin'   => [
                'channel'    => [
                    'upi' => [
                        'name'       => 'UPI',
                        'label'      => 'UPI',
                        'type'       => 'default',
                        'amounts'    => '1000,2000,5000,10000,15000,20000',
                        'min_amount' => 0,
                        'max_amount' => 0
                    ]
                ],
                'min_amount' => 0,
                'max_amount' => 0,
                'amounts'    => '1000,2000,5000,10000,15000,20000',
                'version'    => ['v1.1']
            ]
        ];
    }

    protected function handleNotify($params)
    {
        if ($params) {
            return 'ok';
        }
        return true;
    }


    protected function makeSign($params, $secret): string
    {
        $params = array_filter($params);
        ksort($params);
        $signString = http_build_query($params);
        $signString = urldecode($signString);
        return strtoupper(md5($signString . '&key=' . $secret));
    }

    protected function buildPayParams($orderInfo): array
    {
        $merchant_config = $orderInfo['merchant_config'];
        $params = [
            'appid'        => $merchant_config['app_id'],
            'pay_type'     => $merchant_config['channel'],
            'out_trade_no' => $orderInfo['trade_no'],
            'amount'       => $orderInfo['amount'],
            'callback_url' => $this->getCallbackUrl(),
            'success_url'  => $this->getNotifyUrl(),
            'error_url'    => $this->getNotifyUrl(),
            'version'      => $merchant_config['version'],
            'out_uid'      => $orderInfo['user_id']
        ];
        $params['sign'] = $this->makeSign($params, $merchant_config['private_secret']);
        return $params;
    }

}