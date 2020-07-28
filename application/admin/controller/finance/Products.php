<?php

namespace app\admin\controller\finance;

use app\common\controller\Backend;

/**
 * 金融产品管理
 *
 * @icon fa fa-circle-o
 */
class Products extends Backend
{

    /**
     * Products模型对象
     * @var \app\admin\model\finance\Products
     */
    protected $model = null;

    protected $searchFields = 'title|desc';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\finance\Products;
        $this->view->assign("periodUnitList", $this->model->getPeriodUnitList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("typeList", $this->model->getTypeList());
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


}
