<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Config;

/**
 * 奖期信息
 *
 * @icon fa fa-circle-o
 */
class Issue extends Backend
{
    
    /**
     * Issue模型对象
     * @var \app\admin\model\Issue
     */
    protected $model = null;

    protected $searchFields = 'issue';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Issue;

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


    public function set_code()
    {
        $id = $this->request->post('id');
        $code = $this->request->post('code');
        if(!$id || !$code){
            $this->error('参数错误');
        }
        $digits = Config::get('site.game_code_digits');
        if(strlen($code) != $digits){
            $this->error(strlen($code)."号码位数不对，请输入{$digits}位数字");
        }
        $issue = $this->model::get($id);
        if(!$issue){
            $this->error('奖期不存在');
        }
        $issue->code = $code;
        $issue->statuscode = 1;
        $issue->save();
        $this->success();
    }

    public function clear_set($ids=null)
    {
        $issue = $this->model::get($ids);
        if(!$issue || $issue->statuscode !== 1){
            $this->error('奖期不存在或状态不能更改');
        }
        $issue->statuscode = 0;
        $issue->code = '';
        $issue->save();
        $this->success();
    }
}
