<?php


namespace app\api\controller\fastpay;


use app\api\controller\Fastpay;
use app\common\model\RechargeOrder;
use app\common\model\User;
use think\Db;
use think\Exception;

class Hkkdq extends Fastpay
{
    protected $notifyRequestMethod = 'post';

    protected static function getInfo(): array
    {
        return [
            'name'    => 'hkkdq',
            'label'   => 'Hkkdq',
            'gateway' => [
                'unified' => 'https://www.hkkdq.asia/3part/pay-dev.php'
            ],
            'payin'   => [
                'channel'    => [
                    '1' => [
                        'name'       => 'Online Bank Transfer',
                        'label'      => 'Online Bank Transfer',
                        'type'       => 'default',
                        'amounts'    => '1000,2000,5000,10000,15000,20000',
                        'min_amount' => 0,
                        'max_amount' => 0
                    ],
                ],
                'min_amount' => 0,
                'max_amount' => 0,
                'amounts'    => '1000,2000,5000,10000,15000,20000',
                'version'    => []
            ]
        ];
    }

    public function payin()
    {
        $orderInfo = $this->getOrder()->toArray();
        $params = $this->buildPayParams($orderInfo);
        $this->assign('payurl', $this->getPayUrl() . '?' . http_build_query($params));
        $this->assign('params', []);
        return $this->view->fetch('payview/index');
    }

    protected function buildPayParams($orderInfo): array
    {
        $merchant_config = $orderInfo['merchant_config'];
        $params = [
            'apikey'    => $merchant_config['mch_id'],
            'orderId'   => $orderInfo['trade_no'],
            'money'     => $orderInfo['amount'],
            'submit'    => $merchant_config['channel'],
            'notifyUrl' => $this->getNotifyUrl(),
            'returnUrl' => $this->getCallbackUrl(),
        ];
        $params['sign'] = $this->makeSign($params, $merchant_config['private_secret']);
        return $params;
    }

    protected function makeSign($params, $secret): string
    {
        ksort($params);
        $params_filter = array();
        foreach ($params as $key => $val) {
            if ($key === "sign" || $val === "") {
                continue;
            }
            $params_filter[$key] = $params[$key];
        }
        return md5(http_build_query($params_filter) . $secret);
    }

    protected function getNotifyOrder($params)
    {
        $this->setOrder($params['orderId']);
        return $this->order;
    }

    protected function handleNotify($params, $orderInfo)
    {
        $merchant_config = $orderInfo['merchant_config'];
        if ($this->makeSign($params, $merchant_config['private_secret']) !== $params['sign']) {
            $this->setError('签名错误');
            return false;
        }
        if (strtolower($params['status']) !== 'success') {
            $this->setError('支付失败');
            return false;
        }
        return $params['money'];
    }
}