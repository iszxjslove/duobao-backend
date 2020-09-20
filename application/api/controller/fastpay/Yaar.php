<?php


namespace app\api\controller\fastpay;


use app\common\controller\Api;
use app\common\model\RechargeOrder;
use app\common\model\User;
use fastpay\Yaar as YaarPay;
use think\Exception;
use think\Log;

class Yaar extends Api
{
    protected $noNeedLogin = '*';

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
//                    throw new \Exception('Amount verification failed');
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