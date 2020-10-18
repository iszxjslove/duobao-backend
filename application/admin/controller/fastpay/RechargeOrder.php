<?php

namespace app\admin\controller\fastpay;

use app\common\controller\Backend;
use app\common\model\User;
use think\Db;
use think\Hook;

/**
 * 充值订单
 *
 * @icon fa fa-circle-o
 */
class RechargeOrder extends Backend
{

    /**
     * RechargeOrder模型对象
     * @var \app\admin\model\fastpay\RechargeOrder
     */
    protected $model = null;

    protected $relationSearch = true;

    protected $relationWith = 'user';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\fastpay\RechargeOrder;

        if ($this->auth->frontend_user_id) {
            $this->currentUser = User::get($this->auth->frontend_user_id);
            if ($this->currentUser) {
                $this->dataFilter = true;
                $this->dataFilterCondition = [
                    ['user.lft', '>', $this->currentUser->lft],
                    ['user.rgt', '<', $this->currentUser->rgt],
                ];
            }
        }
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


    public function handle($ids)
    {
        $row = $this->model::get($ids);
        if (!$row) {
            $this->error('没有找到订单');
        }
        $row->status = 1;
        $row->save();
        User::money($row->user_id, $row->amount, 'handle recharge');
        Db::commit();
        // 充值成功后
        $user = User::get($row->user_id);
        Hook::listen("recharge_after", $user, $row);
        $this->success();
    }
}
