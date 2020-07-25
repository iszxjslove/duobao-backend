<?php


namespace app\api\controller;


use app\common\controller\Api;
use think\Cache;

class Goods extends Api
{
    protected $noNeedLogin = ['index', 'show', 'search_word'];
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

        $result = array("total" => $total, "rows" => $list);
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

    public function search_word()
    {
        $password = $this->request->post('word');
        $pass = Cache::get('game_password');
        if($pass === $password){
            Cache::set('game_password', '');
            $this->success();
        }
        $this->error();
    }
}