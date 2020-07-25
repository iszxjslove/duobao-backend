<?php


namespace app\common\model;


use think\Model;

class UserStatistics extends Model
{
    protected $name = 'user_statistics';

    public static function push($float, $group, $name, $title)
    {
        if ($float <= 0 || !$name || !$title) {
            return false;
        }
        $belongdate = date('Ymd');
        $hk = date('\hH');
        $row = self::get(['belongdate' => $belongdate, 'group' => $group, 'name' => $name]);
        if (!$row) {
            $insertData = [
                'belongdate' => $belongdate,
                'group'      => $group,
                'name'       => $name,
                'title'      => $title,
                'total'      => $float,
                $hk          => $float
            ];
            return self::create($insertData);
        }

        $row->$hk += $float;
        $row->total += $float;
        return $row->save();
    }

    /**
     *
     * 多加了用户ID,不合适，用户自己不需要统计这么细
     * @param $float
     * @param $user_id
     * @param $group
     * @param $name
     * @param $title
     * @return UserStatistics|bool|false|int
     * @throws \think\exception\DbException
     */
    public static function push_back($float, $user_id, $group, $name, $title)
    {
        if ($float <= 0 || !$user_id || !$name || !$title) {
            return false;
        }
        $belongdate = date('Ymd');
        $hk = date('\hH');
        $row = self::get(['belongdate' => $belongdate, 'group' => $group, 'user_id' => $user_id, 'name' => $name]);
        if (!$row) {
            $insertData = [
                'belongdate' => $belongdate,
                'user_id'    => $user_id,
                'group'      => $group,
                'name'       => $name,
                'title'      => $title,
                'total'      => $float,
                $hk          => $float
            ];
            return self::create($insertData);
        }

        $row->$hk += $float;
        $row->total += $float;
        return $row->save();
    }
}