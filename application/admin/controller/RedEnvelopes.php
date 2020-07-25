<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\common\controller\Backend;
use think\Db;
use think\Exception;

/**
 * 红包管理
 *
 * @icon fa fa-circle-o
 */
class RedEnvelopes extends Backend
{
    
    /**
     * RedEnvelopes模型对象
     * @var \app\admin\model\RedEnvelopes
     */
    protected $model = null;

    protected $relationSearch = true;

    protected $relationWith = 'admin';

    protected $dataLimit = 'auth';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\RedEnvelopes;
        $this->view->assign("cateList", $this->model->getCateList());
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

    public function details($ids=null)
    {
        $row = $this->model->with('logs.user')->find($ids);
        if(!$row){
            $this->error('数据不存在');
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }


    public function recovery($ids = null)
    {
        $row = $this->model->get($ids);
        if(!$row){
            $this->error('数据不存在');
        }
        if($row->remaining_amount <= 0 || $row->claim_status === 2 || $row->return_status === 1){
            $this->error('红包状态不可操作');
        }
        try {
            Db::startTrans();
            $row->return_status = 1;
            $row->save();
            Admin::money($row->remaining_amount, $row->admin_id, '红包回收');
            Db::commit();
        }catch (Exception $e){
            $this->error($e->getMessage());
        }
        $this->success();
    }
}
