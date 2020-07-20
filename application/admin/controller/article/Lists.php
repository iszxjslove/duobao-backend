<?php

namespace app\admin\controller\article;

use app\admin\model\Article;
use app\common\controller\Backend;
use think\Validate;

/**
 * 文章管理
 *
 * @icon fa fa-circle-o
 */
class Lists extends Backend
{
    
    /**
     * Article模型对象
     * @var Article
     */
    protected $model = null;

    protected $relationSearch = true;

    protected $searchFields = 'title';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Article;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    public function import()
    {
        parent::import();
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with('category')
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->with('category')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->request('row/a');
            $validate = new Validate([
                'name|变量名'  => 'require|unique:article',
            ], [
                'name.unique' => '已存在相同的变量名',
            ]);
            if (!$validate->check($params)) {
                $this->error($validate->getError(), '', $params);
            }
        }
        return parent::add();
    }

    public function edit($ids = null)
    {
        if($this->request->isPost()){
            $params = $this->request->request('row/a');
            $validate = new Validate([
                'name|变量名'  => 'require|unique:article' . ($ids ? ',name,' . $ids : ''),
            ], [
                'name.unique' => '已存在相同的变量名',
            ]);
            if (!$validate->check($params)) {
                $this->error($validate->getError(), '', $params);
            }
        }
        return parent::edit($ids);
    }

    /**
     * 验证变量名是否唯一
     * @param null $id
     */
    public function unique($id = null)
    {
        $params = $this->request->request('row/a');
        $validate = new Validate([
            'name|变量名'  => 'require|unique:article' . ($id ? ',name,' . $id : ''),
        ], [
            'name.unique' => '已存在相同的变量名',
        ]);
        if (!$validate->check($params)) {
            $this->error($validate->getError(), '', $params);
        }
        $this->success();
    }
}
