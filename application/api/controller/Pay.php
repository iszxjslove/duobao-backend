<?php


namespace app\api\controller;


use app\common\controller\Api;
use app\common\model\Fastpay;
use app\common\model\FastpayAccount;
use app\common\model\RechargeOrder;
use app\common\model\UserBank;
use fast\Http;
use fastpay\Tm;
use fastpay\Wintec;
use fastpay\Yaar;
use fastpay\Zow;
use think\Db;
use think\Exception;
use think\Log;
use think\Request;

class Pay extends Api
{
    protected $noNeedLogin = '*';

    public function paytype()
    {
        $list = FastpayAccount::where(['status' => 1])
            ->field('id,fastpay,channel,amount_list,min_amount,max_amount,custom_amount,desc,status,title')
            ->select();
        if ($list) {
            $list = collection((array)$list)->toArray();
        }
        foreach ($list as $key => &$item) {
            unset($item['fastpay_config']);
        }
        unset($item);
        $this->success('', $list);
    }

    public function unified()
    {
        $amount = $this->request->post('amount', 0, 'intval');
        $account_id = $this->request->post('account_id', 0, 'intval');
        $bank_id = $this->request->post('bank_id', 0, 'intval');
        $account = FastpayAccount::get($account_id);
        if (!$account || $account->status !== 1) {
            $this->error('Access maintenance');
        }
        if ($account->min_amount > 0 && $amount < $account->min_amount) {
            $this->error('Minimum amount ' . $account->min_amount);
        }
        if ($account->max_amount > 0 && $amount > $account->max_amount) {
            $this->error('Maximum amount ' . $account->max_amount);
        }
        if ($account->channel_type === 'bank' && !$bank_id) {
            $this->error('Please select bank card');
        }
        $other_params = [];
        if ($bank_id) {
            $other_params['bank'] = UserBank::get($bank_id);
            if (!$other_params['bank']) {
                $this->error('Invalid card');
            }
        }
        $recharge = new RechargeOrder;
        $extend = [
            'first_recharge' => $this->auth->first_recharge_time ? 0 : 1
        ];
        $order = $recharge->createOrder($this->auth->id, $amount, $account->toArray(), $other_params, $extend);
        if (!$order) {
            $this->error('Order creation failed');
        }
        $payurl = url(implode('/', ['pay', $account->fastpay, 'payin']), ['trade_no' => $order->trade_no]);
        $this->success('', ['payurl' => $payurl]);
    }

    public function unified_tm()
    {
        $amount = $this->request->request('amount');
        $channel = $this->request->request('channel');
        if (!$amount || $amount < 1) {
            $this->error('Wrong amount');
        }
        $pay = new Tm();
        $merchantConfig = [
            'secret'     => 'b8e438bcd53a4f4e601743dba058f71a67239f24',
            'customerid' => '20080182'
        ];

        Db::startTrans();
        try {
            $orderInfo = [
                'user_id'         => $this->auth->id,
                'trade_no'        => time(),
                'amount'          => bcmul($amount, 1, 2),
                'create_time'     => time(),
                'product_title'   => 'Jewellery',
                'merchant_config' => json_encode($merchantConfig),
                'status'          => 0,
            ];
            RechargeOrder::create($orderInfo);
            $option = [
                'version' => '1.0'
            ];
            $params = $pay->buildParams(array_merge($orderInfo, $option));
            $payUrl = $pay->getPayUrl();
            $response = Http::post($payUrl, $params);
            dump($response);
            dump($params);
            exit;
//
//            if (!$response) {
//                $this->error('recharge fail');
//            }
////            $response = json_decode($response, true);
//            $this->error('', $response);
//            if ($response['code'] !== '200' || !$response['url']) {
//                $this->error('Network exception, please try again later');
//            }

            $output['payurl'] = url('payview/index') . '?' . http_build_query($params);
            Db::commit();
        } catch (\think\Exception $e) {
            $this->error($e->getMessage());
            Db::rollback();
        }
        $this->success('', $output);
    }

    public function unified_back3()
    {
        $amount = $this->request->request('amount');
        $channel = $this->request->request('channel');
        if (!$amount || $amount < 1) {
            $this->error('Wrong amount');
        }
        $pay = new Yaar();
        $merchantConfig = [
            'appId'  => '5be25a05c13c45eea4ef04124044694d',
            'secret' => 'mTtFtQPOXxXcKWAWBdFTRRfOmsBOubpq66VUGBpGA9b6QsQldNDBYxGegW6xl2GR5ak1i7CQ76qRtk6by81Mvvx2rylO9B6oiNsHZJX71yWufNvKs4GVKTr4ADu2t9dA',
            'mchId'  => 'MCR-20209-R00058'
        ];

        Db::startTrans();
        try {
            $orderInfo = [
                'user_id'         => $this->auth->id,
                'trade_no'        => time(),
                'amount'          => bcmul($amount, 1, 2),
                'create_time'     => time(),
                'product_title'   => 'Jewellery',
                'merchant_config' => json_encode($merchantConfig),
                'status'          => 0,
            ];
            RechargeOrder::create($orderInfo);
            $option = [
                'channelId' => $channel,
                'currency'  => 'inr',
                'version'   => '1.0'
            ];
            if ((int)$channel === 8036) {
                $bankId = $this->request->request('bankId');
                $bank = UserBank::get($bankId);
                if (!$bank) {
                    $this->error('no bank card');
                }
                $option['depositName'] = $bank->actual_name;
                $option['depositAccount'] = $bank->account_number;
            }
            $params = $pay->buildParams(array_merge($orderInfo, $option));
            $params['payUrl'] = $pay->getPayUrl();
//            $response = Http::post($params['payUrl'], $params);
//            dump($params);exit;
//
//            if (!$response) {
//                $this->error('recharge fail');
//            }
////            $response = json_decode($response, true);
//            $this->error('', $response);
//            if ($response['code'] !== '200' || !$response['url']) {
//                $this->error('Network exception, please try again later');
//            }

            $output['payurl'] = url('payview/index') . '?' . http_build_query($params);
            Db::commit();
        } catch (\think\Exception $e) {
            $this->error($e->getMessage());
            Db::rollback();
        }
        $this->success('', $output);
    }

    public function unified_back2()
    {
        $amount = $this->request->request('amount');
        if (!$amount || $amount < 1) {
            $this->error('Wrong amount');
        }

        // 选择支付
        $pay = new Zow();
        $merchantConfig = ['merchant_id' => '200825122', 'secret' => 'i3pnio1dfbn8jagnyzrzpylb7gfgu9gq'];

        Db::startTrans();
        try {
            $orderInfo = [
                'user_id'         => $this->auth->id,
                'trade_no'        => \NumberPool::getOne(),
                'amount'          => $amount,
                'create_time'     => time(),
                'product_title'   => 'Jewellery',
                'merchant_config' => json_encode($merchantConfig),
                'status'          => 0,
            ];
            RechargeOrder::create($orderInfo);
            $params = $pay->buildParams($orderInfo);
            $payUrl = $pay->getPayUrl();
            $response = Http::post($payUrl, $params);

            Log::write($params);
            Log::write($response);

            if (!$response) {
                $this->error('recharge fail');
            }
            $response = json_decode($response, true);

            if ($response['code'] !== '200' || !$response['url']) {
                $this->error('Network exception, please try again later');
            }
            $output['payurl'] = $response['url'];
            Db::commit();
        } catch (\think\Exception $e) {
            $this->error($e->getMessage());
            Db::rollback();
        }
        $this->success('', $output);
    }

    public function unified_back()
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