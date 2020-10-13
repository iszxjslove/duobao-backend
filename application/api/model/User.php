<?php


namespace app\api\model;

/**
 * Class User
 * @package app/api/model
 * @property int id ID
 * @property int pid 上级ID
 * @property string referrer 推荐码
 * @property int group_id 组别ID
 * @property string username 用户名
 * @property string nickname 昵称
 * @property string password 密码
 * @property string salt 密码盐
 * @property string payment_password 支付密码
 * @property string email 电子邮箱
 * @property string mobile 手机号
 * @property string avatar 头像
 * @property int level 等级
 * @property int gender 性别
 * @property string birthday 生日
 * @property string bio 格言
 * @property float money 余额
 * @property float hold_balance 冻结余额
 * @property float financial_money 余额宝
 * @property int score 积分
 * @property int successions 连续登录天数
 * @property int maxsuccessions 最大连续登录天数
 * @property int prevtime 上次登录时间
 * @property int logintime 登录时间
 * @property string loginip 登录IP
 * @property int loginfailure 失败次数
 * @property string joinip 加入IP
 * @property int jointime 加入时间
 * @property int createtime 创建时间
 * @property int updatetime 更新时间
 * @property string token Token
 * @property string status 状态
 * @property string verification 验证
 * @property int first_recharge 首充
 * @property int depth 深度
 * @property int lft 左范围
 * @property int rgt 右范围
 */
class User extends \app\common\model\User
{

}