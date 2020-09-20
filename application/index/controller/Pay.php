<?php


namespace app\index\controller;


use app\common\controller\Frontend;
use fast\Http;
use fastpay\Zow;

class Pay extends Frontend
{
    protected $noNeedLogin = '*';

    public function index()
    {
        $pay = new Zow();
        $orderInfo = [
        ];
        $params = $pay->buildParams($orderInfo);
        $payUrl = $pay->getPayUrl();
//        $option[CURLOPT_HTTPHEADER] = ["Content-Type: application/json", "Accept: text/html"];
        $this->view->assign('payurl', $payUrl);
        $this->view->assign('params', $params);
        return $this->view->fetch();
    }

    public function yaar()
    {
        return $this->view->fetch();
    }
}