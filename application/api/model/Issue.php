<?php


namespace app\api\model;


use app\common\model\IssueSales;
use think\Model;



/**
 * Class Issue
 * @package app/api/model
 * @property int id 奖期ID 
 * @property int game_id 游戏ID
 * @property string code 开奖号码
 * @property int last_digits 最后一位
 * @property string colors 中奖颜色
 * @property string issue 奖期号
 * @property string belongdate 属于哪天的奖期
 * @property int salestart 本期销售开始时间
 * @property int saleend 本期销售结束时间
 * @property int earliestwritetime 最早录号时间
 * @property int canneldeadline 本期平台停止撤单时间
 * @property int statuscode 开奖奖期状态 0:未写入;1:写入待验证;2:已验证;
 * @property int statuscheckbonus 检查中奖状态(0:未开始;1:进行中;2:已经完成)
 * @property int statusbonus 返奖状态(0:未开始;1:进行中;2:已经完成)
 */
class Issue extends Model
{
    protected $name = 'game_issue';

    public function sales()
    {
        return $this->hasOne('\app\common\model\IssueSales', 'issue_id', 'id');
    }
}