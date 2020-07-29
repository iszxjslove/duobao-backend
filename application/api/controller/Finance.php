<?php


namespace app\api\controller;


use app\api\model\finance\Account;
use app\api\model\finance\Products;
use app\common\controller\Api;
use app\common\model\UserFinance;
use app\common\model\UserFinanceOrder;
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
        $result = Account::opening($this->auth->getUser());
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
        $account = UserFinance::get(['user_id' => $this->auth->id]);
        $this->success('', $account);
    }

    /**
     * 金融产品
     * @throws DbException
     */
    public function products()
    {
        $list = Products::all();
        $this->success('', $list);
    }

    /**
     * 余额转入
     */
    public function balance_into()
    {
        $amount = $this->request->post('amount');
        $product_id = $this->request->post('product_id');
        $product = Products::get($product_id);
        if (!$product) {
            $this->error('product not exist');
        }
        if ($amount <= 0) {
            $this->error('Invalid amount');
        }
        if ($this->auth->money < $amount) {
            $this->error('Sorry, your credit is running low');
        }

        $period = $product->period;
        $period_unit = $product->period_unit;
        if ($product->interest_settlement_time === 'day') {
            $period = 1;
            $period_unit = 'day';
        }
        $next_period_time = strtotime("+{$period} {$period_unit}");
        $end_time = $product->type === 'regular' ? strtotime("+{$product->period} {$product->period_unit}") : 0;
        $insertData = [
            'user_id'                  => $this->auth->id,
            'financial_products_id'    => $product_id,
            'trade_no'                 => make_sn(),                 // 订单号
            'title'                    => $product->title,           // 标题
            'desc'                     => $product->desc,            // 描述
            'type'                     => $product->type,            // 类型
            'period'                   => $product->period,          // 周期
            'period_unit'              => $product->period_unit,     // 周期单位
            'rate'                     => $product->rate,            // 利率
            'interest_where'           => $product->interest_where,           // 利息去向
            'interest_settlement_time' => $product->interest_settlement_time,           // 结息时间
            'contract_amount'          => $amount,           // 合同金额
            'remaining_amount'         => $amount,           // 剩余合同金额
            'next_period_time'         => $next_period_time,           // 最近一期计息时间
            'end_time'                 => $end_time,           // 结束时间
            'status'                   => 1,           // 状态1计息2结束
        ];
        try {
            Db::startTrans();
            UserFinanceOrder::create($insertData);
            \app\common\model\User::money($this->auth->getUser(), -$amount, 'Transfer to finance');
            UserFinance::contract($this->auth->getUser(), $amount, $product->title);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('', $product);
    }
}