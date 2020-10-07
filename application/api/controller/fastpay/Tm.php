<?php


namespace app\api\controller\fastpay;


use app\api\controller\Fastpay;

class Tm extends Fastpay
{
    public static function getInfo(): array
    {
        return [
            'name'    => 'tm',
            'label'   => '牛屎哥支付',
            'gateway' => [
                'unified' => 'http://tmpay.pw/check/'
            ],
            'payin'   => [
                'channel'    => [
                    'quickpay' => [
                        'name'       => 'Online Bank Transfer',
                        'label'      => 'Online Bank Transfer',
                        'type'       => 'bank',
                        'amounts'    => '1000,2000,5000,10000,15000,20000',
                        'min_amount' => 0,
                        'max_amount' => 0
                    ],
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
            'version'    => '1.0',            // 版本号		varchar(5)	是	默认1.0
            'customerid' => $merchant_config['mch_id'],         // 商户编号		int(8)	是	商户后台获取
            'sdorderno'  => $orderInfo['trade_no'],          // 商户订单号		varchar(20)	是	可用时间戳加随机数，不要超过18位
            'total_fee'  => $orderInfo['amount'],          // 订单金额		decimal(10,2)	是	精确到小数点后两位，例如10.24
            'paytype'    => $merchant_config['channel'],            // 支付编号		varchar(10)	是	详见银行编码、、或者非银行编码
            'notifyurl'  => $this->getNotifyUrl(),          // 异步通知URL		varchar(50	是	不能带有任何参数
            'returnurl'  => $this->getCallbackUrl(),          // 同步跳转URL		varchar(50)	是	不能带有任何参数
            'is_qrcode'  => 3,          // 二维码_URL地址		tinyint(1)	否	is_qrcode=3 支付地址 is_qrcode=2 支付二维码（部分不支持） is_qrcode=1 云捷处理后的地址（部分不支持）
        ];

        if (!empty($other_params['bank'])) {
            $params["bankcode"] = $other_params['bank']['bank_code'];
        }
        $params['sign'] = $this->makeSign($params, $merchant_config['private_secret']);
        return $params;
    }

    protected function makeSign($params, $secret): string
    {
        $sortKey = ['version', 'customerid', 'total_fee', 'sdorderno', 'notifyurl', 'returnurl'];
        $string = '';
        foreach ($sortKey as $key) {
            $string .= trim($key) . "=" . trim($params[$key]) . '&';
        }
        return md5($string . $secret);
    }

    protected function handleNotify($params)
    {
        // TODO: Implement handleNotify() method.
    }

}