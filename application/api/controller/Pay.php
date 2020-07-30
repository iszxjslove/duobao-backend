<?php


namespace app\api\controller;


use app\common\controller\Api;
use fast\Http;
use fastpay\Wintec;
use think\Request;

class Pay extends Api
{
    protected $noNeedLogin = '*';

    public function unified()
    {
        $amount = $this->request->request('amount');
        if (!$amount || $amount < 1) {
            $this->error('Wrong amount');
        }

        $pay = new Wintec();
        $orderInfo = [
            'appid'         => '0000000001',
            'trade_no'      => \NumberPool::getOne(),
            'product_title' => 'Jewellery',
            'amount'        => $amount,
            'secret'        => 'bc2d5fc0c8d2442d86c9f4fd2d4a0b6b'
        ];
        $params = $pay->buildParams($orderInfo);
        $payUrl = $pay->getPayUrl();
        $option[CURLOPT_HTTPHEADER] = ["Content-Type: application/json", "Accept: text/html"];
        try {
            $response = Http::post($payUrl, json_encode($params), $option);
            if (!$response) {
                $this->error('recharge fail');
            }
            $response = json_decode($response, true);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('', ['payurl' => $response['data']['url']]);
    }
}