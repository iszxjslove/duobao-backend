<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\common\controller\Backend;
use app\common\model\UserStatistics;
use fast\Random;
use think\Db;
use think\Exception;
use think\Validate;

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

    public function details($ids = null)
    {
        $row = $this->model->with('logs.user')->find($ids);
        if (!$row) {
            $this->error('数据不存在');
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $validate = new Validate([
                    'amount' => 'require|number|min:1',
                    'number' => 'require|number|min:1',
                    'cate'   => 'require',
                ]);
                if (!$validate->check($params)) {
                    $this->error($validate->getError());
                }
                switch ($params['cate']) {
                    case 'lucky':
                        if (bcdiv($params['amount'], $params['number'], 2) < 0.01) {
                            throw new Exception('单个红包不可低于0.01');
                        }
                        $params['total_amount'] = $params['amount'];
                        break;
                    case 'fixed':
                        $params['total_amount'] = bcmul($params['amount'], $params['number'], 2);
                        break;
                }
                $params['remaining_amount'] = $params['total_amount'];
                $params['remaining_number'] = $params['number'];
                if ($this->auth->money < $params['total_amount']) {
                    throw new Exception('余额不足');
                }
                $params['code'] = Random::numeric(14);
                $params['token'] = md5(md5(Random::alnum(20)) . $params['code']);
                $params['expiry_time'] = time() + 86400;
                $params['admin_id'] = $this->auth->id;
                $result = false;
                Db::startTrans();
                try {
                    $result = $this->model->allowField(true)->save($params);
                    Admin::money(-$params['total_amount'], $this->auth->id, '发送红包');
                    // 统计红包创建
                    UserStatistics::push('create_red_envelopes', $params['total_amount'], 'red_envelopes');
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }


    public function recovery($ids = null)
    {
        $row = $this->model::get($ids);
        if (!$row) {
            $this->error('数据不存在');
        }
        if ($row->remaining_amount <= 0 || $row->claim_status === 2 || $row->return_status === 1) {
            $this->error('红包状态不可操作');
        }
        try {
            Db::startTrans();
            $row->return_status = 1;
            $row->save();
            Admin::money($row->remaining_amount, $row->admin_id, '红包回收');
            // 统计红包回收
            UserStatistics::push('recovery_red_envelopes', $row->remaining_amount, 'red_envelopes');
            Db::commit();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success();
    }
}
