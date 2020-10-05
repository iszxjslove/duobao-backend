<?php


namespace app\api\controller\fastpay;


use app\api\controller\Fastpay;
use app\common\model\RechargeOrder;
use app\common\model\User;

class Yaar extends Fastpay
{
    public static function getInfo(): array
    {
        return [
            'name'    => 'yaar',
            'label'   => 'YAAR',
            'gateway' => [
                'unified' => 'https://api.247yp.site/api/v1/payin/pay_info'
            ],
            'payin'   => [
                'channel'    => [
                    '8035' => [
                        'name'       => 'Online Bank Transfer',
                        'label'      => 'Online Bank Transfer',
                        'type'       => 'default',
                        'amounts'    => '1000,2000,5000,10000,15000,20000',
                        'min_amount' => 0,
                        'max_amount' => 0
                    ],
                    '8036' => [
                        'name'       => 'P2A Transfer',
                        'label'      => 'P2A Transfer',
                        'type'       => 'bank',
                        'amounts'    => '1000,2000,5000,10000,15000,30000',
                        'min_amount' => 0,
                        'max_amount' => 0
                    ]
                ],
                'min_amount' => 0,
                'max_amount' => 0,
                'amounts'    => '1000,2000,5000,10000,15000,20000',
                'version'    => '1.0'
            ]
        ];
    }

    protected function buildPayParams($orderInfo): array
    {
        $merchant_config = $orderInfo['merchant_config'];
        $other_params = $orderInfo['other_params'];
        $params = [
            'amount'     => bcmul($orderInfo['amount'], 100),
            'appId'      => $merchant_config['app_id'],
            'channelId'  => $merchant_config['channel'],
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
        return $params;
    }

    protected function makeSign($params, $secret): string
    {
        ksort($params);
        $string = '';
        foreach ($params as $key => $value) {
            if ($value === '' || $key === 'sign') {
                continue;
            }
            $string .= trim($key) . "=" . trim($value) . '&';
        }
        return strtoupper(md5($string . 'key=' . $secret));
    }


    public function payoutaaa()
    {
        $merchantConfig = [
            'appId'  => '5be25a05c13c45eea4ef04124044694d',
            'secret' => 'mTtFtQPOXxXcKWAWBdFTRRfOmsBOubpq66VUGBpGA9b6QsQldNDBYxGegW6xl2GR5ak1i7CQ76qRtk6by81Mvvx2rylO9B6oiNsHZJX71yWufNvKs4GVKTr4ADu2t9dA',
            'mchId'  => 'MCR-20209-R00058'
        ];

        $order = [
            'accountName'    => 'aaaaaa',
            'accountNo'      => 'bbbbbb',
            'amount'         => 100000,
            'mchId'          => $merchantConfig['mchId'],
            'trade_no'       => time(),
            'notifyUrl'      => 'fdsafdsafds',
            'payoutBankCode' => 'fdsafdsafdsa',
            'reqTime'        => time(),
            'ifscCode'       => 'fdsafdsa',
            'secret'         => $merchantConfig['secret']
        ];
    }

    protected function handleNotify($params)
    {
        if ((int)$params['status'] === 2) {
            $order = RechargeOrder::getByTradeNo($params['mchOrderNo']);
            if (!$order) {
                throw new \Exception('fail');
            }
            if ($order->status === 1) {
                die('OK');
            }
            if ($order->status !== 0) {
                throw new \Exception('fail');
            }
            $merchant_config = json_decode($order['merchant_config'], true);
            $yaar = new YaarPay();
            $sign = $yaar->makeSign($params, $merchant_config['secret']);
            if ($sign !== $params['sign']) {
                throw new \Exception('Invalid Signature');
            }
            $amount = bcdiv($params['amount'], 100, 2);
            $diff = $order->amount - $amount;
            if ($diff < -1 || $diff > 1) {
                throw new \Exception('Amount verification failed');
            }
            $order->amount = $amount;
            User::money($order->user_id, $amount, 'yaar payment');
            $order->status = 1;
            $order->save();
            die('OK');
        }
    }
}