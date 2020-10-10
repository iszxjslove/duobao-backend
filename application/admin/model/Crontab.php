<?php

namespace app\admin\model;

use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Model;




/**
 * Class Crontab
 * @package app/admin/model
 * @property int id 计划任务ID
 * @property string name 任务名称
 * @property string title 任务标题
 * @property string parameter 参数,以,分隔
 * @property int islocked 是否锁定:0正常，1锁定
 * @property int update_time 最后更新时间
 */
class Crontab extends Model
{


    // 表名
    protected $name = 'crontab';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'update_time_text',
        'create_time_text'
    ];

    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['update_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['create_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    /**
     * 锁开头
     * @param string $name 任务名称
     * @param string $title 任务名称
     * @param string $parameter 参数，以,号分开
     * @param bool $lock 是否锁定:0正常，1锁定
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function switchLock($name = '', $parameter = '', $lock = TRUE, $title = '')
    {
        $condition['name'] = $name;
        if ($parameter !== "") {
            $condition['parameter'] = $parameter;
        }
        if ($lock === TRUE) {//锁定计划任务
            //检测计划任务数据是否存
            $check = $this->where($condition)->find();
            if (empty($check)) {
                $insertData = [];
                $insertData['name'] = $name;
                $insertData['update_time'] = time();
                $insertData['title'] = $title;
                if ($parameter !== "") {
                    $insertData['parameter'] = $parameter;
                }
                $insertData['islocked'] = 1;
                $result = $this->insert($insertData);
                if (!$result) {
                    return FALSE;
                }
            } else {
                $updateData = [];
                $updateData['islocked'] = 1;
                $updateData['update_time'] = time();
                $condition['islocked'] = 0;
                $result = $this->where($condition)->update($updateData);
                if (!$result) {
                    return FALSE;
                }
            }
        } else {
            $updateData = [];
            $updateData['islocked'] = 0;
            $updateData['update_time'] = time();
            $condition['islocked'] = 1;
            $result = $this->where($condition)->update($updateData);
            if (!$result) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * 获取被锁的计划任务 (默认600秒)
     * @param int $timeSec
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function crontabUnlockList($timeSec = 300)
    {
        $timeSec = (int)$timeSec;
        $condition = ['islocked' => 1];
        return $this->where($condition)->whereTime('update_time', "-{$timeSec} seconds")->select();
    }

    /**
     * 计划任务解锁
     * @param $ids
     * @param int $timeSec 默认5分钟内的
     * @return bool|false|string|Collection
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function crontabUnlock($ids, $timeSec = 300)
    {
        if (empty($ids) || !is_array($ids)) {
            return FALSE;
        }
        $timeSec = (int)$timeSec;
        foreach ($ids as $key => $value) {
            if (!is_numeric($value)) {
                unset($ids[$key]);
            }
        }
        if (count($ids) === 0) {
            return FALSE;
        }
        $condition = [
            'id' => ['in', $ids]
        ];

        return $this->where($condition)->whereTime('update_time', "-{$timeSec} seconds")->save(['islocked' => 0]);
    }

    /**
     * 计划任务自动解锁
     * @return mixed
     */
    public function crontabAutoUnlock()
    {
        // 运行 crontab  解锁时间
        $timeSec = 1800;
        $condition = [
            'islocked' => 1,
            'name'     => ['!=', 'dealcode']
        ];
        $result = $this->where($condition)->whereTime('update_time', "-{$timeSec} seconds")->save(['islocked' => 0]);
        return !($result === FALSE);
    }

    /**
     * 更新计划任务
     * @param string $name
     * @param string $parameter
     * @return Crontab
     */
    public function updateCrontab($name = '', $parameter = '')
    {
        $condition['name'] = $name;
        if ($parameter !== "") {
            $condition['parameter'] = $parameter;
        }
        return $this->where($condition)->update(['update_time' => time()]);
    }
}
