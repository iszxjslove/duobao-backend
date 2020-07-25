<?php


namespace app\admin\controller;


use app\common\controller\Backend;
use app\admin\model\RedEnvelopesLog as RedEnvelopesLogModel;

class RedEnvelopesLog extends Backend
{
    /**
     * RedEnvelopes模型对象
     * @var RedEnvelopesLogModel
     */
    protected $model = null;

    protected $relationSearch = true;

    protected $relationWith = 'user';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new RedEnvelopesLogModel();
    }
}