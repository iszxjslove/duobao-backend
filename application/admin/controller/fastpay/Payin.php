<?php

namespace app\admin\controller\fastpay;

use app\common\model\FastpayAccount;
use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Payin extends Backend
{
    
    /**
     * Fastpay_account模型对象
     * @var FastpayAccount
     */
    protected $model = null;

    protected $fastpayList = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new FastpayAccount;
        $this->fastpayList = $this->model->getFastpay('payin');
        $this->view->assign('fastpayList', $this->fastpayList);
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

    public function getFastpayList()
    {
        return json($this->fastpayList);
    }
}
