<?php


namespace app\api\controller\fastpay;


use app\api\controller\Fastpay;
use app\common\model\RechargeOrder;
use app\common\model\User;

class Yaar extends Fastpay
{
    protected function getNotifyOrder($params)
    {
        $this->setOrder($params['orderId']);
        return $this->order;
    }

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
                'version'    => ['1.0']
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

    protected function handleNotify($params, $orderInfo)
    {
        $merchant_config = $orderInfo['merchant_config'];
        if ($this->makeSign($params, $merchant_config['private_secret']) !== $params['sign']) {
            $this->setError('签名错误');
            return false;
        }
        if ((int)$params['status'] !== 2) {
            $this->setError('支付失败');
            return false;
        }
        return bcdiv($params['amount'], 100, 2);
    }
}