<?php

namespace app\admin\controller\user;

use app\api\model\FeeLog;
use app\common\controller\Backend;
use app\common\model\TeamBonusApply;
use think\Db;

/**
 * 佣金申请记录
 *
 * @icon fa fa-circle-o
 */
class Bonusapply extends Backend
{

    /**
     * Bonusapply模型对象
     * @var \app\admin\model\user\Bonusapply
     */
    protected $model = null;

    protected $relationSearch = true;

    protected $relationWith = 'user';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\user\Bonusapply;

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
     * 同意
     */
    public function agree($ids)
    {
        $list = $this->model::all(explode(',', $ids));
        foreach ($list as $item) {
            if($item->status === 0){
                Db::startTrans();
                try {
                    $item->status = 1;
                    $item->check_time = time();
                    $item->save();
                    FeeLog::where(['apply_id' => $item->id])->update(['status' => 2, 'receive_time' => time()]);
                    \app\common\model\User::money($item->user_id, $item->amount, 'bonus to balance');
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
            }
        }
        $this->success();
    }

    /**
     * 拒绝
     */
    public function refuse($ids)
    {
        $list = $this->model::all(explode(',', $ids));
        foreach ($list as $item) {
            if($item->status === 0){
                Db::startTrans();
                try {
                    $item->status = -1;
                    $item->check_time = time();
                    $item->save();
                    FeeLog::where(['apply_id' => $item->id])->update(['status' => 0, 'receive_time' => time()]);
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
            }
        }
        $this->success();
    }
}
