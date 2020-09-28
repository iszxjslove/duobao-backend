<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\RechargeOrder;
use fast\Http;
use fastpay\Tm;
use fastpay\Yaar;

class Payment extends Frontend
{
    protected $noNeedLogin = '*';

    protected $trade_no = '';

    protected $order = null;

    /**
     * @var RechargeOrder
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new RechargeOrder;
        $this->trade_no = $this->request->param('trade_no');
        if ($this->trade_no) {
            $this->order = $this->model->getByTradeNo($this->trade_no);
            if (!$this->order) {
                $this->error('Order does not exist');
            }
        }else{
            $this->error('Params Error');
        }
    }

    public function yaar()
    {
        $pay = new Yaar();
        $orderInfo = $this->order->toArray();
        $orderInfo['other_params']['currency'] = 'inr';
        $orderInfo['other_params']['version'] = '1.0';
        $result = $pay->payin($orderInfo);
//        $response = Http::get($result['gateway'], $result['params']);
//        $output = json_decode($response, true);
//        if(!$output){
//            $this->error('System error');
//        }
//        if (!empty($output['errCode'])) {
//            $errs = [
//                '0034' => 'Invalid Deposit Name',
//                '0035' => 'Invalid Deposit Account'
//            ];
//            $msg = $errs[$output['errCode']] ?? 'Error code:' . $output['errCode'];
//            $this->error($msg);
//        }
        $this->assign('payurl', $result['gateway']);
        $this->assign('params', $result['params']);
        return $this->view->fetch('index');
    }

    public function tm()
    {
        $pay = new Tm();
        $orderInfo = $this->order->toArray();
        $result = $pay->payin($orderInfo);
        $response = Http::get($result['gateway'], $result['params']);
        $response = json_decode($response, true);
        if(!$response){
            $this->error('System error');
        }
        if ((string)$response['status'] !== '1') {
            $this->error($response['msg']);
        }
        $this->assign('payurl', $result['gateway']);
        $this->assign('params', $result['params']);
        return $this->view->fetch('index');
    }
}