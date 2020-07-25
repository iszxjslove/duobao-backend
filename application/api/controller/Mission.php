<?php


namespace app\api\controller;


use app\common\controller\Api;
use app\common\model\Mission as MissionModel;
use app\common\model\UserMission;

class Mission extends Api
{
    protected $model = null;

    protected $dataLimit = false;

    public function lists()
    {
        $this->model = new MissionModel();
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $total = $this->model
            ->with('usermission')
            ->where($where)
            ->order($sort, $order)
            ->count();

        $list = $this->model
            ->with('usermission')
            ->where($where)
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->select();
        return json(['total' => $total, 'rows' => $list]);
    }

    public function receive()
    {
        $id = $this->request->request('id');
        $row = MissionModel::get($id);
        $now_time = time();
        // 判断任务状态
        if(!$row || $row->status !== 1 || $row->start_time > $now_time || $row->end_time < $now_time || !$row->still_some || $row->still_some <= 0){
            $this->error('no mission');
        }
        // 判断重复
        $my = UserMission::get(['user_id'=>$this->auth->id, 'mission_id'=>$row->id]);
        if($my){
            $this->error('no need to repeat');
        }
        $result = UserMission::receive($this->auth->getUser(), $row);
        if(!$result){
            $this->error('no receive');
        }
        $this->success();
    }
}