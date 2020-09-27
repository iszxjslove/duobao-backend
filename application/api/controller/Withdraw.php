<?php


namespace app\api\controller;


use app\common\controller\Api;
use app\common\model\User;
use app\common\model\WithdrawOrder;
use think\Config;
use think\Db;

class Withdraw extends Api
{
    /**
     * @var WithdrawOrder
     */
    protected $model;

    protected function _initialize()
    {
        parent::_initialize();
        $this->model = new WithdrawOrder();
    }

    /**
     * 提现
     */
    public function add()
    {
        $amount = $this->request->post('amount');
        $bank_id = $this->request->post('bank_id');
        $min_amount = \think\Config::get('site.min_withdraw_amount');
        if ($amount < $min_amount) {
            $this->error("Minimum amount {$min_amount}");
        }
        if ($this->auth->money < $amount) {
            $this->error('Sorry, your credit is running low');
        }
        try {
            $order = $this->model->createOrder($this->auth->id, $amount, $bank_id);
            if (!$order) {
                throw new \Exception('Order creation failed');
            }
            User::money($this->auth->id, -$amount, 'Balance withdrawal');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('', ['trade_no' => $order->trade_no]);
    }
}