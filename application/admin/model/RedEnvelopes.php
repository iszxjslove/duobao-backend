<?php

namespace app\admin\model;

use fast\Random;
use think\Config;
use think\Exception;
use think\Model;


class RedEnvelopes extends Model
{


    // 表名
    protected $name = 'red_envelopes';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'cate_text',
        'create_time_text',
        'expiry_time_text',
        'url'
    ];

    protected static function init()
    {
        self::beforeInsert(static function ($row) {
//            $data = $row->toArray();
            $admin = Admin::get($row->admin_id);
            if (!$admin) {
                throw new Exception('管理员不存在');
            }
            switch ($row->cate) {
                case 'lucky':
                    if (bcdiv($row->amount, $row->number, 2) < 0.01) {
                        throw new Exception('单个红包不可低于0.01');
                    }
                    $row->total_amount = $row->amount;
                    break;
                case 'fixed':
                    $row->total_amount = bcmul($row->amount, $row->number, 2);
                    break;
            }
            $row->remaining_amount = $row->total_amount;
            $row->remaining_number = $row->number;
            if ($admin->money < $row->total_amount) {
                throw new Exception('余额不足');
            }

            $row->code = Random::numeric(14);
            $row->token = md5(md5(Random::alnum(20)) . $row->code);
            $row->expiry_time = time() + 86400;
        });

        self::afterInsert(static function ($row) {
            $data = $row->toArray();
            $admin = Admin::get($data['admin_id']);
            if (!$admin) {
                throw new Exception('管理员不存在');
            }
            if ($admin->money < $row->total_amount) {
                throw new Exception('余额不足');
            }
            Admin::money(-$row->total_amount, $data['admin_id'], '发送红包');
        });
    }

    public function getCateList()
    {
        return ['lucky' => __('Lucky'), 'fixed' => __('Fixed')];
    }

    public function getUrlAttr($value, $data)
    {
        $url = rtrim(Config::get('site.frontend_url'), '/');
        return "{$url}/page/envelopes/open?code={$data['code']}&token={$data['token']}";
    }


    public function getCateTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['cate']) ? $data['cate'] : '');
        $list = $this->getCateList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getExpiryTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['expiry_time']) ? $data['expiry_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setExpiryTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function admin()
    {
        return $this->belongsTo('Admin', 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function logs()
    {
        return $this->hasMany('RedEnvelopesLog', 'red_edvelopes_id', 'id');
    }
}
