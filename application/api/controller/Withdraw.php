<?php


namespace app\api\controller;


use app\api\model\WithdrawOrder;
use app\common\controller\Api;
use app\common\model\User;
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
        $this->model = new WithdrawOrder;
    }

    /**
     * 提现
     */
    public function add()
    {
        $amount = $this->request->post('amount');
        $card_id = $this->request->post('card_id');
        $min_amount = Config::get('site.min_withdraw_amount');
        if ($amount < $min_amount) {
            $this->error("Minimum amount {$min_amount}");
        }
        if ($this->auth->money < $amount) {
            $this->error('Sorry, your credit is running low');
        }
        $result = false;
        try {
            Db::startTrans();
            $result = $this->model->createOrder($this->auth->id, $amount, $card_id);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error('fail');
        }
        $money = User::where('id', $this->auth->id)->value('money');
        $this->success('', ['money' => $money]);
    }
}