<?php

namespace app\admin\controller;

use app\api\model\Issue;
use app\common\controller\Backend;
use app\common\model\UserStatistics;
use think\Config;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        $seventtime = \fast\Date::unixtime('day', -7);
        $paylist = $createlist = [];
        for ($i = 0; $i < 7; $i++) {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $createlist[$day] = mt_rand(20, 200);
            $paylist[$day] = mt_rand(1, mt_rand(1, $createlist[$day]));
        }
        $hooks = config('addons.hooks');
        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
        $addonComposerCfg = ROOT_PATH . '/vendor/karsonzhang/fastadmin-addons/composer.json';
        Config::parse($addonComposerCfg, "json", "composer");
        $config = Config::get("composer");
        $addonVersion = isset($config['version']) ? $config['version'] : __('Unknown');
        $this->view->assign([
            'totaluser'        => 35200,
            'totalviews'       => 219390,
            'totalorder'       => 32143,
            'totalorderamount' => 174800,
            'todayuserlogin'   => 321,
            'todayusersignup'  => 430,
            'todayorder'       => 2324,
            'unsettleorder'    => 132,
            'sevendnu'         => '80%',
            'sevendau'         => '32%',
            'paylist'          => $paylist,
            'createlist'       => $createlist,
            'addonversion'     => $addonVersion,
            'uploadmode'       => $uploadmode
        ]);

        $UserStatistics = new UserStatistics;
        // 累计数据
        $statistics_list = $UserStatistics->group('category')->field('category,sum(total) as total')->select();
        $statistics = [];
        foreach ($statistics_list as $item) {
            $statistics[$item['category']] = $item['total'];
        }
        // 十天数据
        $ten_list = $UserStatistics->where(['belongdate' => ['>', date('Y-m-d', strtotime('-2 day'))]])->select();
        $ten_data = [];
        foreach ($ten_list as $item) {
            $ten_data[$item['belongdate']][$item['category']] = $item->toArray();
        }
        // 今日数据
        $today_data = $ten_data[date('Y-m-d')] ?? [];

        $this->view->assign('ten_data', $ten_data);
        $this->view->assign('today_data', $today_data);
        $this->view->assign('statistics', $statistics);
        return $this->view->fetch();
    }

    public function data()
    {
        $keys = $this->request->get('keys');
        $list = [];
        if ($keys) {
            $keys = explode(',', $keys);
            foreach ($keys as $key) {
                if (method_exists($this, $key)) {
                    $list[$key] = $this->$key();
                }
            }
        }
        return json($list);
    }

    public function issue_sales()
    {
        $currentIssue = Issue::get(['saleend' => ['>', time()]]);
        $currentIssueSales = $currentIssue && $currentIssue->sales ? $currentIssue->sales->toArray() : [];
        $numbers = [];
        for ($i = 0; $i < 10; $i++) {
            $numbers[$i] = $currentIssueSales["EE{$i}"] ?? 0.00;
        }
        $sort = array_unique($numbers);
        arsort($sort);
        return [
            'issue'   => $currentIssue,
            'sales'   => $currentIssueSales,
            'numbers' => $numbers,
            'first'   => current($sort),
            'second'  => next($sort),
            'third'   => next($sort) ?: 0,
        ];
    }
}
