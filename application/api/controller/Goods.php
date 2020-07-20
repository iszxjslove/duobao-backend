<?php


namespace app\api\controller;


use app\common\controller\Api;

class Goods extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function index()
    {
        $Goods = new \app\admin\model\Goods();
        $search = $this->request->get("search", '');
        $sort = $this->request->get("sort", 'id');
        $order = $this->request->get("order", "DESC");
        $offset = $this->request->get("offset", 0);
        $limit = $this->request->get("limit", 0);
        $where = [];
        if ($search) {
            $where['title'] = ['like', "%{$search}%"];
        }
        $total = $Goods
            ->where($where)
            ->order($sort, $order)
            ->count();

        $list = $Goods
            ->where($where)
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->select();
        $result = array("total" => $total, "rows" => $list, 'where' => $where);
        return json($result);
    }

    public function show($id = null)
    {
        $goods = \app\admin\model\Goods::get($id);
        if ($goods) {
            $this->success('', $goods);
        }
        $this->error();
    }
}