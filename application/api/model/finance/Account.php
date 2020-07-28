<?php

namespace app\api\model\finance;

use app\common\model\User;
use think\Model;


class Account extends Model
{


    // 表名
    protected $name = 'user_finance';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text',
        'status_text'
    ];

    protected $insert = [
        'balance'         => 0,
        'contract_amount' => 0,
        'interest'        => 0
    ];


    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function user()
    {
        return $this->belongsTo('\app\admin\model\user', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /**
     * 开通金融账户
     * @param User $user
     * @return Account
     */
    public static function opening(User $user)
    {
        $insertData = [
            'user_id'         => $user->id,
            'balance'         => 0,
            'contract_amount' => 0,
            'interest'        => 0
        ];
        
        return self::create($insertData);
    }
}
