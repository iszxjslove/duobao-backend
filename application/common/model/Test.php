<?php


namespace app\common\model;


use think\Model;



/**
 * Class Test
 * @package app/common/model
 * @property int id 
 * @property string test 
 * @property string type 
 * @property string text 
 */
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