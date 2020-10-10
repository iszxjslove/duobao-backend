<?php

namespace app\api\model;

use think\Model;



/**
 * Class Game
 * @package app/api/model
 * @property int id 游戏ID
 * @property string name 名称
 * @property string title 标题
 * @property int cycle 周期时长
 * @property string issuerule 奖期规则
 * @property string issueset 奖期设置
 * @property string moneys 单注金额
 * @property int max_hands 单期最大购买注数
 * @property string green_ordinary 绿色普通
 * @property float green_ordinary_odds 绿色普通赔率
 * @property string green_lucky 绿色幸运
 * @property float green_lucky_odds 绿色幸运赔率
 * @property string red_ordinary 红色普通
 * @property float red_ordinary_odds 红色普通赔率
 * @property string red_lucky 红色幸运
 * @property float red_lucky_odds 红色幸运赔率
 * @property string violet 紫色
 * @property float violet_odds 紫色赔率
 * @property string singular 单个数字
 * @property float singular_odds 单个数字赔率
 * @property int update_time 更新时间
 * @property int create_time 创建时间
 * @property string status 状态
 */
class Game extends Model
{
    protected $name = 'game';
}