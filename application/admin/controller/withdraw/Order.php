<?php

namespace app\admin\controller\withdraw;

use app\common\controller\Backend;
use app\common\model\Fastloan;
use app\common\model\WithdrawOrder;
use fast\Http;
use fastpay\Yaar;
use think\exception\DbException;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Order extends Backend
{

    /**
     * Order模型对象
     * @var \app\admin\model\withdraw\Order
     */
    protected $model = null;

    protected $relationSearch = true;

    protected $relationWith = 'user,admin';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new WithdrawOrder;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    public function import()
    {
        parent::import();
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 通过
     * @param null $ids
     * @throws DbException
     */
    public function adopt($ids = null)
    {
        $row = $this->model::get($ids);
        if (!$row) {
            $this->error('订单不存在');
        }
        if ($this->request->isPost()) {
            $account = Fastloan::get(['status' => 1]);
            $row->merchant_config = $account;
            $pay = new Yaar();
            $result = $pay->payout($row->toArray());
            $response = Http::post($result['gateway'], $result['params']);
            $response = json_decode($response, true);
            if ($response && !empty($response['errCode'])) {
                $errs = [
                    '0034' => 'Invalid Deposit Name',
                    '0035' => 'Invalid Deposit Account',
                    '0044' => 'Invalid Payout Bank Code'
                ];
                $msg = $errs[$response['errCode']] ?? 'Error code:' . $response['errCode'];
                $this->error($msg);
            }
            $row->status = 1;
            $result = $row->save();
            if(!$result){
                $this->error();
            }
            $this->success('');
        }
        $this->error('不允许');
    }


    /**
     * 驳回
     * @param null $ids
     */
    public function reject($ids = null)
    {
        $row = $this->model::get($ids);
        if (!$row) {
            $this->error('订单不存在');
        }
        if ($this->request->isPost()) {
            $row->status = 1;
            $result = $row->save();
            if(!$result){
                $this->error();
            }
            $this->success('');
        }
        $this->error('不允许');
    }

}
