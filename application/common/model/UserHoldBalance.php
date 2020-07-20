<?php


namespace app\common\model;


use think\Db;
use think\Exception;
use think\exception\DbException;
use think\Model;

class UserHoldBalance extends Model
{
    protected $name = 'user_hold_balance_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text',
        'update_time_text',
        'status_text'
    ];

    public function statuslist($key = '')
    {
        $list = ['释放', '冻结'];
        if ($key !== '') {
            return $list[$key] ?? $key;
        }
        return $list;
    }

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

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        return $this->statuslist($value);
    }

    public function setCreateTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    public function setCanneldeadlineAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    /**
     * 冻结用户余额
     * @param $user_id
     * @param $money
     * @param $memo
     * @param int $admin_id
     * @param string $admin_name
     * @return bool
     * @throws DbException
     */
    public function hold($user_id, $money, $memo, $admin_id = 0, $admin_name = '')
    {
        $user = User::get($user_id);
        if (!$user) {
            $this->error = 'user does not exist';
            return false;
        }
        if ($user->money < $money) {
            $this->error = 'Insufficient Balance';
            return false;
        }
        $insertData = [
            'user_id'    => $user_id,
            'money'      => $money,
            'memo'       => $memo,
            'admin_id'   => $admin_id,
            'admin_name' => $admin_name,
            'status'     => 1,
        ];
        try {
            Db::startTrans();
            $this->save($insertData);
            User::money(-$money, $user->id, $memo);
            User::hold_balance($this->id, $money, $user->id, $memo);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
        return true;
    }
}