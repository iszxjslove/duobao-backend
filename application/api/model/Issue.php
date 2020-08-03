<?php


namespace app\api\model;


use app\common\model\IssueSales;
use think\Model;

class Issue extends Model
{
    protected $name = 'game_issue';

    public function sales()
    {
        return $this->hasOne('\app\common\model\IssueSales', 'issue_id', 'id');
    }
}