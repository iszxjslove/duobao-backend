<?php


namespace app\api\controller;


use app\common\controller\Api;
use app\common\model\Category;
use app\common\model\Complaint as ComplaintModel;

class Complaint extends Api
{
    protected $model = null;

    public function index()
    {
        $this->model = new ComplaintModel();
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $total = $this->model
            ->where($where)
            ->order($sort, $order)
            ->count();

        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->select();
        return json(['total' => $total, 'rows' => $list]);
    }

    public function category()
    {
        $list = Category::all(['type'=>'work']);
        return json($list);
    }

    public function add()
    {
        $category_id = $this->request->post('category_id', '', 'int');
        $desc = $this->request->post('desc', '');
        $whatsapp = $this->request->post('whatsapp', '');
        $result  = ComplaintModel::create([
            'user_id' => $this->auth->id,
            'category_id' => $category_id,
            'desc' => $desc,
            'whatsapp' => $whatsapp
        ]);

        if(!$result){
            $this->error('fail');
        }
        $this->success();
    }
}