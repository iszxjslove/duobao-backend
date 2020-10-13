<?php


namespace app\api\model;


use think\Model;

/**
 * Class Admin
 * @package app/api/model
 * @property int id ID
 * @property string username 用户名
 * @property string nickname 昵称
 * @property string password 密码
 * @property string salt 密码盐
 * @property float money 余额
 * @property string avatar 头像
 * @property string email 电子邮箱
 * @property int loginfailure 失败次数
 * @property int logintime 登录时间
 * @property string loginip 登录IP
 * @property int createtime 创建时间
 * @property int updatetime 更新时间
 * @property string token Session标识
 * @property string status 状态
 */
class Admin extends Model
{

}