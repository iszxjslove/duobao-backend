<?php

namespace app\admin\controller\article;

use app\common\controller\Backend;
use app\common\model\Category as CategoryModel;

/**
 * 文章管理
 *
 * @icon fa fa-circle-o
 */
class Category extends Backend
{
    /**
     * @var CategoryModel
     */
    protected $model = null;

    protected $dataFilter = true;

    protected $dataFilterCondition = [];

    protected $categoryType = 'article';

    protected $categoryList = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new CategoryModel;
        $this->dataFilterCondition['type'] = $this->categoryType;
        $this->categoryList = $this->model->getCategoryTree($this->categoryType);
        $categoryData = [0 => ['name' => __('None')]];
        foreach ($this->categoryList as $k => $v) {
            $categoryData[$v['id']] = $v;
        }
        $this->view->assign("flagList", $this->model->getFlagList());
        $this->view->assign("parentList", $categoryData);
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
            $search = $this->request->request("search");
            //构造父类select列表选项数据
            $list = [];
            if ($search) {
                foreach ($this->categoryList as $k => $v) {
                    if (stripos($v['name'], $search) !== false || stripos($v['nickname'], $search) !== false) {
                        $list[] = $v;
                    }
                }
            } else {
                $list = $this->categoryList;
            }
            $total = count($list);
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }

}
