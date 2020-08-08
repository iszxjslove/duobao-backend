<?php

namespace app\admin\controller;

use app\api\model\Issue;
use app\api\model\WithdrawOrder;
use app\common\controller\Backend;
use app\common\model\Projects;
use app\common\model\RechargeOrder;
use app\common\model\User;
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

    public function statistics()
    {
        $this->request->filter('trim');
        $op = trim($this->request->param('op', 'today'));
        $range = $this->request->param('range/a');
        if ($op !== 'between') {
            $range = null;
        }
        $UserStatistics = new UserStatistics;
        $list = $UserStatistics->whereTime('belongdate', $op, $range)->field(true)->select();
        $data = [];
        foreach ($list as $item) {
            $data[$item['belongdate']][$item['name']] = $item;
        }
        return json($data);
    }

    public function countTotal()
    {
        $this->request->filter('trim');
        $op = trim($this->request->param('op', 'today'));
        $range = $this->request->param('range/a');
        if ($op !== 'between') {
            $range = null;
        }
        $UserStatistics = new UserStatistics;
        $data = $UserStatistics->whereTime('belongdate', $op, $range)->group('name')->field('category,name,sum(total) as total')->select();
        return json($data);
    }

    public function countPeople()
    {
        $this->request->filter('trim');
        $op = trim($this->request->get('op', 'today'));
        $range = $this->request->get('range/a');
        if ($op !== 'between') {
            $range = null;
        }
        $count['register'] = User::whereTime('jointime', $op, $range)->count();
        $count['recharge'] = RechargeOrder::whereTime('completion_time', $op, $range)
            ->group('user_id')
            ->where(['status' => (new RechargeOrder)->getCurrentTableFieldConfig('status.success.value')])
            ->count();
        $count['withdraw'] = WithdrawOrder::whereTime('completion_time', $op, $range)
            ->group('user_id')
            ->where(['status' => (new WithdrawOrder)->getCurrentTableFieldConfig('status.success.value')])
            ->count();
        $count['projects'] = Projects::whereTime('create_time', $op, $range)
            ->group('user_id')
            ->count();
        return json($count);
    }

    /**
     * 总余额
     */
    public function totalAmount()
    {
        $total_balance = User::sum('money');
        return json(['balance' => $total_balance]);
    }

    public function total()
    {
        $data = UserStatistics::group('name')->field('category,name,sum(total) as total')->select();
        return json($data);
    }
}
