<?php


namespace app\api\controller;


use app\common\controller\Api;
use app\common\model\UserBank;
use think\Db;

class Bank extends Api
{
    protected $noNeedRight = '*';

    /**
     * @var UserBank
     */
    protected $model;


    protected function _initialize()
    {
        parent::_initialize();
        $this->model = new UserBank;
    }

    public function lists()
    {

        $list = $this->model::all(['user_id' => $this->auth->id]);
        return json(['total' => count($list), 'rows' => $list]);
    }

    public function add()
    {
        $params = $this->request->request('row/a');
        if ($this->auth->getEncryptPaymentPassword($params['payment_password']) !== $this->auth->payment_password) {
            $this->error('Password error');
        }
        unset($params['payment_password']);
        $params['user_id'] = $this->auth->id;
        $validate = new \think\Validate([
            'actual_name' => 'require',
            'account_number' => 'require|unique:user_bank',
        ]);
        if(!$validate->check($params)){
            $this->error($validate->getError());
        }
        $result = $this->model->allowField(true)->save($params);
        if ($result === false) {
            $this->error($this->model->getError());
        }
        $this->success();
    }

    public function set_default($id = null)
    {
        $row = $this->model::get(['id' => $id, 'user_id' => $this->auth->id]);
        if (!$row) {
            $this->error('Bank card does not exist');
        }
        $result = false;
        try {
            Db::startTrans();
            $this->model->where('user_id', $this->auth->id)->update(['is_default' => 0]);
            $row->is_default = 1;
            $result = $row->save();
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error('update failed');
        }
        $this->success();
    }
}