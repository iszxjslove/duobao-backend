<?php


namespace app\common\model;


use think\Model;

class UserStatistics extends Model
{
    protected $name = 'user_statistics';

    public static function push($name, $float = 1, $category = '')
    {
        if ($float <= 0 || !$name) {
            return false;
        }
        $belongdate = date('Ymd');
        $hk = date('\hH');
        $row = self::get(['belongdate' => $belongdate, 'category' => $category, 'name' => $name]);
        if (!$row) {
            $insertData = [
                'belongdate' => $belongdate,
                'category'   => $category ?: $name,
                'name'       => $name,
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