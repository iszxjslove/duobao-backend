<?php


namespace app\common\model;


use think\Model;

class Test extends Model
{
    protected $name = 'test';

    public $nestedConfig = [
        'leftKey'    => 'lft',
        'rightKey'   => 'rgt',
        'levelKey'   => 'depth',
        'parentKey'  => 'pid',
        'primaryKey' => 'id',
    ];
}