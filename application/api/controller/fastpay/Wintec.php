<?php

namespace app\api\controller\fastpay;


use fast\Http;

class Wintec
{
    public function notify()
    {
        $pay = new \fastpay\Wintec();
        $orderInfo = [
            'appid'         => '0000000001',
            'trade_no'      => '1231231312',
            'product_title' => 'thisis.testproduct',
            'amount'        => '10',
            'secret'        => 'bc2d5fc0c8d2442d86c9f4fd2d4a0b6b'
        ];
        $params = $pay->buildParams($orderInfo);
        ksort($params);
        $signstr = \fastpay\Wintec::parseSignStr($params) . "&key={$orderInfo['secret']}";

        dump($signstr);

        dump('ee2406f9713f9cc234a4e2bb176b1968c500455866e0509a86896264c38dbd20');
        $payUrl = $pay->getPayUrl();
//dump(json_encode($params));
        dump($params);

//        $a = "amount=10&appId=1001412384&country=IN&currency=INR&extInfo=paymentTypes=credit,debit,ewallet,upi&merTransNo=1576735973586&notifyUrl=http://yoursite.com/notifyurl&prodName=southeast.asia&returnUrl=http://yoursite.com/returnurl&version=1.1&key=bc2d5fc0c8d2442d86c9f4fd2d4a0b6b";
//       $b = "amount=10&appId=1001412384&country=IN&currency=INR&extInfo=paymentTypes=credit,debit,ewallet,upi&merTransNo=1576735973586&notifyUrl=http://yoursite.com/notifyurl&prodName=southeast.asia&returnUrl=http://yoursite.com/returnurl&version=1.1&key=bc2d5fc0c8d2442d86c9f4fd2d4a0b6b";
//       dump($a === $b);

                $option[CURLOPT_HTTPHEADER] = ["Content-Type: application/json", "Accept: text/html"];
        $response = Http::post($payUrl, json_encode($params), $option);
        dump($response);
    }
}