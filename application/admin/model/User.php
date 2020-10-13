<?php

namespace app\admin\model;

use app\common\model\MoneyLog;
use app\common\model\ScoreLog;
use fast\Random;
use think\Model;

/**
 * Class User
 * @package app/admin/model
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
class User extends Model
{

    // 表名
    protected $name = 'user';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'prevtime_text',
        'logintime_text',
        'jointime_text'
    ];


    public $nestedConfig = [
        'leftKey'    => 'lft',
        'rightKey'   => 'rgt',
        'levelKey'   => 'depth',
        'parentKey'  => 'pid',
        'primaryKey' => 'id',
    ];

    public function getOriginData()
    {
        return $this->origin;
    }

    protected static function init()
    {
        self::beforeUpdate(function ($row) {
            $changed = $row->getChangedData();
            //如果有修改密码
            if (isset($changed['password'])) {
                if ($changed['password']) {
                    $salt = \fast\Random::alnum();
                    $row->password = \app\common\library\Auth::instance()->getEncryptPassword($changed['password'], $salt);
                    $row->salt = $salt;
                } else {
                    unset($row->password);
                }
            }
            //如果有修改密码
            if (isset($changed['payment_password'])) {
                if ($changed['payment_password']) {
                    $row->payment_password = md5($changed['payment_password']);
                    $row->salt = $salt;
                } else {
                    unset($row->payment_password);
                }
            }
        });


        self::beforeUpdate(function ($row) {
            $changedata = $row->getChangedData();
            if (isset($changedata['money'])) {
                $origin = $row->getOriginData();
                MoneyLog::create(['user_id' => $row['id'], 'money' => $changedata['money'] - $origin['money'], 'before' => $origin['money'], 'after' => $changedata['money'], 'memo' => '管理员变更金额']);
            }
            if (isset($changedata['score'])) {
                $origin = $row->getOriginData();
                ScoreLog::create(['user_id' => $row['id'], 'score' => $changedata['score'] - $origin['score'], 'before' => $origin['score'], 'after' => $changedata['score'], 'memo' => '管理员变更积分']);
            }
        });
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['referrer' => Random::id2code($row[$pk])]);
        });
    }

    public function getGenderList()
    {
        return ['1' => __('Male'), '0' => __('Female')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getPrevtimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['prevtime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getLogintimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['logintime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getJointimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['jointime'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setPrevtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setLogintimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setJointimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setBirthdayAttr($value)
    {
        return $value ? $value : null;
    }

    public function group()
    {
        return $this->belongsTo('UserGroup', 'group_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function parentUser()
    {
        return $this->belongsTo('User', 'pid', 'id', [], 'LEFT');
    }
}
