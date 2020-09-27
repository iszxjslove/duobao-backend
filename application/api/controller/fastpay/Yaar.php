<?php


namespace app\api\controller\fastpay;


use app\common\controller\Api;
use app\common\model\RechargeOrder;
use app\common\model\User;
use fast\Http;
use fastpay\Yaar as YaarPay;
use think\Exception;
use think\Log;

class Yaar extends Api
{
    protected $noNeedLogin = '*';

    public function payout()
    {
        $yaar = new YaarPay();

        $merchantConfig = [
            'appId'  => '5be25a05c13c45eea4ef04124044694d',
            'secret' => 'mTtFtQPOXxXcKWAWBdFTRRfOmsBOubpq66VUGBpGA9b6QsQldNDBYxGegW6xl2GR5ak1i7CQ76qRtk6by81Mvvx2rylO9B6oiNsHZJX71yWufNvKs4GVKTr4ADu2t9dA',
            'mchId'  => 'MCR-20209-R00058'
        ];

        $order = [
            'accountName' => 'aaaaaa',
            'accountNo' => 'bbbbbb',
            'amount' => 100000,
            'mchId' => $merchantConfig['mchId'],
            'trade_no' => time(),
            'notifyUrl' => 'fdsafdsafds',
            'payoutBankCode' => 'fdsafdsafdsa',
            'reqTime' => time(),
            'ifscCode'=>'fdsafdsa',
            'secret'=>$merchantConfig['secret']
        ];
        $result = $yaar->payout($order);
        $response = Http::post($result['gateway'],  $result['params']);
        dump($response);
    }

    public function notify()
    {
        $data = $this->request->get();
        try {
            if (!$data) {
                throw new \Exception('fail');
            }
            if ((int)$data['status'] === 2) {
                $order = RechargeOrder::getByTradeNo($data['mchOrderNo']);
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
                $sign = $yaar->makeSign($data, $merchant_config['secret']);
                if ($sign !== $data['sign']) {
                    throw new \Exception('Invalid Signature');
                }
                $amount = bcdiv($data['amount'], 100, 2);
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
        } catch (\Exception $e) {
            die($e->getMessage());
//            Log::write($e->getMessage());
//            Log::write($data);
        }
    }
}