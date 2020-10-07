<?php


namespace app\api\controller;


use app\api\model\finance\Account;
use app\api\model\finance\Products;
use app\common\controller\Api;
use app\common\model\UserFinance;
use app\common\model\UserFinanceOrder;
use app\common\model\YuEBao;
use app\common\model\YuEBaoOrder;
use app\common\model\YuEBaoProducts;
use think\Db;
use think\Exception;
use think\exception\DbException;

/**
 * Class Finance
 * @package app\api\controller
 */
class Finance extends Api
{
    /**
     * 开通账户
     */
    public function opening()
    {
        $result = YuEBao::create([
            'user_id'      => $this->auth->id,
            'balance'      => 0,
            'sum_interest' => 0,
            'status'       => 'normal'
        ]);
        if (!$result) {
            $this->error();
        }
        $this->success();
    }

    /**
     * 我的账户
     * @throws DbException
     */
    public function account()
    {
        $account = YuEBao::get(['user_id' => $this->auth->id]);
        $this->success('', $account);
    }

    /**
     * 金融产品
     * @throws DbException
     */
    public function products()
    {
        $list = YuEBaoProducts::all(['status' => 'normal']);
        $this->success('', $list);
    }

    /**
     * 余额转入
     */
    public function balance_into()
    {
        $amount = $this->request->post('amount');
        $product_id = $this->request->post('product_id');
        $product = YuEBaoProducts::get($product_id);
        if (!$product) {
            $this->error('product not exist');
        }
        if ($amount <= 0) {
            $this->error('Invalid amount');
        }
        if ($this->auth->money < $amount) {
            $this->error('Sorry, your credit is running low');
        }
        try {
            Db::startTrans();
            \app\common\model\User::money($this->auth->id, -$amount, 'Transfer to finance');
            YuEBaoOrder::transferIn($product, $this->auth->id, $amount);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('', $product);
    }
}