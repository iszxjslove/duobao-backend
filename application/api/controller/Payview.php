<?php


namespace app\api\controller;


use app\common\controller\Api;

class Payview extends Api
{
    protected $noNeedLogin = '*';

    public function index()
    {
        $params = $this->request->get();
        $payurl = $params['payUrl'];
        unset($params['payUrl']);
        $view = view();
        $view->assign('payurl', $payurl);
        $view->assign('params', $params);
        return $view;
    }
}