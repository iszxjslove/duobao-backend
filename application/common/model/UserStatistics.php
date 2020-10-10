<?php


namespace app\common\model;


use think\Model;



/**
 * Class UserStatistics
 * @package app/common/model
 * @property int id 佣金统计ID
 * @property string belongdate 日期
 * @property string category 分类
 * @property string name 名称
 * @property float total 合计
 * @property float h01 01:00:00
 * @property float h02 02:00:00
 * @property float h03 03:00:00
 * @property float h04 04:00:00
 * @property float h05 05:00:00
 * @property float h06 06:00:00
 * @property float h07 07:00:00
 * @property float h08 08:00:00
 * @property float h09 09:00:00
 * @property float h10 10:00:00
 * @property float h11 11:00:00
 * @property float h12 12:00:00
 * @property float h13 13:00:00
 * @property float h14 14:00:00
 * @property float h15 15:00:00
 * @property float h16 16:00:00
 * @property float h17 17:00:00
 * @property float h18 18:00:00
 * @property float h19 19:00:00
 * @property float h20 20:00:00
 * @property float h21 21:00:00
 * @property float h22 22:00:00
 * @property float h23 23:00:00
 * @property float h00 00:00:00
 */
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