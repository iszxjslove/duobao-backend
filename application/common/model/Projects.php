<?php


namespace app\common\model;


use think\Model;

/**
 * Class Projects
 * @package app/common/model
 * @property int id 方案ID
 * @property int user_id 用户ID
 * @property int game_id 游戏ID
 * @property string issue 奖期号
 * @property int issue_id 奖期ID
 * @property string no_colors 中奖颜色
 * @property string no_code 中奖号码
 * @property string color 颜色
 * @property string code 号码
 * @property string selected 用户选择
 * @property float singleprice 单倍价格
 * @property int multiple 倍数
 * @property float totalprice 总共价格
 * @property float maxbouns 最高奖金
 * @property float bonus 实际派发的奖金
 * @property string userip 用户IP
 * @property string cdnip 服务器IP
 * @property float contract_amount 合同金额
 * @property float fee 手续费
 * @property int create_time 方案生成时间
 * @property int update_time 方案更新时间
 * @property int deducttime 真实扣款时间
 * @property int bonustime 奖金派发时间
 * @property int isgetprize 中奖状态(0:未判断;1:中奖;2:未中奖)
 * @property int prizestatus 派奖状态(0:未派;1:已派)
 */
class Projects extends Base
{

    // 表名
    protected $name = 'projects';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text',
        'update_time_text'
    ];

    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['update_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }
}