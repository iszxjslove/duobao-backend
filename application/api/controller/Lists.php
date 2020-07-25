<?php


namespace app\api\controller;


use app\common\controller\Api;
use app\common\model\MoneyLog;

class Lists extends Api
{
    public function billing()
    {
        $limit = $this->request->request('limit', 10, 'int');
        $limit = $limit > 100 ? 100 : $limit;
        $page = $this->request->request('page', 1, 'int');
        $offset = ($page - 1) * $limit;
        $projectsModel = new MoneyLog();
        $where = [
            'user_id' => $this->auth->id
        ];
        $total = $projectsModel
            ->where($where)
            ->order('id', 'desc')
            ->count();
        $list = $projectsModel
            ->where($where)
//            ->field('issue,code,color,totalprice,create_time,isgetprize,prizestatus,bonustime')
            ->limit($offset, $limit)
            ->order('id', 'desc')
            ->select();
        return json(['total' => $total, 'rows' => $list]);
    }
}