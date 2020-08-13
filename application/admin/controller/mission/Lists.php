<?php

namespace app\admin\controller\mission;

use app\admin\model\Mission;
use app\common\controller\Backend;
use function Sodium\add;

/**
 * 任务管理
 *
 * @icon fa fa-circle-o
 */
class Lists extends Backend
{

    /**
     * Lists模型对象
     * @var Mission
     */
    protected $model = null;

    protected $relationWith = 'admin';

    protected $relationSearch = true;

    protected $dataLimit = 'auth';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Mission;
        $statusList = $this->model->getStatusList();
        $this->view->assign('statusList', $statusList);
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


    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params['cycle_time'] = strtotime("+{$params['cycle']} {$params['cycle_unit']}") - time();
                $params['surplus_amount'] = $params['amount_limit'];
            }
            $this->request->post(['row' => $params]);
        }
        return parent::add();
    }

}
