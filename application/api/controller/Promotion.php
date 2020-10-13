<?php


namespace app\api\controller;


use app\api\model\FeeLog;
use app\common\controller\Api;
use app\common\model\TeamBonusApply;
use think\Config;
use app\common\model\User;
use think\Db;
use think\Exception;

class Promotion extends Api
{
    protected $noNeedRight = '*';


    public function count()
    {
        $maxLevel = Config::get('site.max_team_level');
        $condition = [
            'lft'   => ['>', $this->auth->lft],
            'rgt'   => ['<', $this->auth->rgt],
            'depth' => [
                ['>', $this->auth->depth],
                ['<', $this->auth->depth + $maxLevel]
            ]
        ];
        $active_members_today = User::where($condition)->whereTime('logintime', 'today')->count();
        $bonus = FeeLog::where(['status' => 0, 'user_id' => $this->auth->id])->sum('money');
        $child = [];
        for ($i = 1; $i < $maxLevel; $i++) {
            $condition['depth'] = $this->auth->depth + $i;
            $child[$i] = [
                'total_people' => User::where($condition)->count(),
                'contribution' => FeeLog::where(['level' => $i, 'user_id' => $this->auth->id])->sum('money')
            ];
        }
        $data = [
            'bonus'                => $bonus,
            'active_members_today' => $active_members_today,
            'child'                => $child
        ];
        $this->success('', $data);
    }

    public function bonusRecord()
    {
        $where = ['user_id' => $this->auth->id];
        $order = 'id desc';
        $limit = $this->request->request('limit', 10);
        $page = $this->request->request('page', 1);
        $total = FeeLog::where($where)
            ->order($order)
            ->count();
        $list = FeeLog::where($where)
            ->order($order)
            ->page($page, $limit)
            ->select();
        $result = array("total" => $total, "rows" => $list);
        $this->success('', $result);
    }

    public function applyRecord()
    {
        $where = ['user_id' => $this->auth->id];
        $order = 'id desc';
        $limit = $this->request->request('limit', 10);
        $page = $this->request->request('page', 1);
        $total = TeamBonusApply::where($where)
            ->with('records')
            ->order($order)
            ->count();
        $list = TeamBonusApply::where($where)
            ->with('records')
            ->order($order)
            ->page($page, $limit)
            ->select();
        $result = array("total" => $total, "rows" => $list);
        $this->success('', $result);
    }

    public function promotionRecord()
    {
        $order = 'id desc';
        $limit = $this->request->request('limit', 10);
        $page = $this->request->request('page', 1);
        $condition = [
            'lft'   => ['>', $this->auth->lft],
            'rgt'   => ['<', $this->auth->rgt],
            'depth' => $this->auth->depth + 2
        ];
        $total = User::where($condition)
            ->order($order)
            ->count();
        $list = User::where($condition)
            ->order($order)
            ->field('id,jointime,joinip,logintime,loginip,first_recharge,username,nickname')
            ->page($page, $limit)
            ->select();
        $result = array("total" => $total, "rows" => $list);
        $this->success('', $result);
    }

    public function applyToBalance()
    {
        Db::startTrans();
        try {
            FeeLog::where(['user_id' => $this->auth->id, 'status' => 0])->update(['status' => -1]);
            $amount = FeeLog::where(['user_id' => $this->auth->id, 'status' => -1])->sum('money');
            if($amount <= 0){
                throw new Exception('no bonus');
            }
            $log = TeamBonusApply::create(['user_id' => $this->auth->id, 'amount' => $amount, 'status' => 0]);
            FeeLog::where(['user_id' => $this->auth->id, 'status' => -1])->update(['status' => 1, 'apply_id' => $log->id, 'receive_time' => time()]);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success();
    }
}