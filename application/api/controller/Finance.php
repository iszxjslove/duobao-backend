<?php


namespace app\api\controller;


use app\api\model\finance\Account;
use app\api\model\finance\Products;
use app\common\controller\Api;
use app\common\model\UserFinance;

class Finance extends Api
{
    public function opening()
    {
        $result = Account::opening($this->auth->getUser());
        if(!$result){
            $this->error();
        }
        $this->success();
    }

    public function account()
    {
        $account = UserFinance::get(['user_id' => $this->auth->id]);
        $this->success('', $account);
    }

    public function products()
    {
        $list = Products::all();
        $this->success('', $list);
    }
}