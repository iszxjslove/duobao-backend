<?php


namespace app\api\controller;


use addons\smswinic\library\Smswinic;
use app\api\model\WithdrawOrder;
use app\common\controller\Api;
use app\common\model\Projects;
use app\common\model\RechargeOrder;
use app\common\model\UserStatistics;
use app\common\model\User;
use fast\Random;
use sms\Winic;
use think\Db;

class Test extends Api
{
    protected $noNeedLogin = ['*'];

    public function test()
    {
//        $recharge = new RechargeOrder;
//        $order = $recharge->createOrder(1, 10000, ['aaa'], ['bbbb']);
//
//        $order = $recharge->get(1);
//        if($order && $order->status !== 1){
//            $order->status = 1;
//            $order->save();
//        }
//
//        dump($order->toArray());
//        $sms = new Winic();
        $mobile = '+918182002000';
        $event = 'register';
////        $code = '85263552157';
//        $response = $sms->sendInternationalMessages($code, 'hello,847584');
//        dump($response);

        $sms = Smswinic::instance();
        $sms->mobile($mobile);
        $sms->msg('hello,'.time());
        $ret = $sms->send();
        if(!$ret) {
            dump($sms->getError());
        }
        dump($ret);
    }

    public function count()
    {
        $this->request->filter('trim');
        $op = trim($this->request->param('op', 'today'));
        $range = $this->request->param('range/a');
        if ($op !== 'between') {
            $range = null;
        }
        $count['register'] = User::whereTime('jointime', $op, $range)->value('count(id) as count');
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
        $count['balance'] = User::sum('money');
        return json($count);
    }


    public function makeWithdrawOrder()
    {
        set_time_limit(0);
        echo '运行前内存：' . round(memory_get_usage() / 1024 / 1024, 2) . 'MB' . "\r\n";
        $day = $this->request->request('day', 1);
        $model = new WithdrawOrder;
//        Db::execute('TRUNCATE TABLE fa_withdraw_order');
        $start_time = $model->order('completion_time')->limit(1)->value('completion_time');
        if (!$start_time) {
            $start_time = time();
        }
        $status = $model->getCurrentTableFieldConfig('status.value_to_names');
        // 多少天
        for ($d = 0; $d < $day; $d++) {
            $time = strtotime("-{$d} day", $start_time);
            // 每天多少人
            $un = random_int(10, 1000);
            $order = [];
            for ($u = 1; $u <= $un; $u++) {
                // 每人多少笔
                $on = random_int(1, 100);
                for ($o = 0; $o < $on; $o++) {
                    $order[] = [
                        'user_id'         => $u,
                        'amount'          => random_int(100, 10000),
                        'completion_time' => $time,
                        'status'          => array_rand($status)
                    ];
                }
            }
            WithdrawOrder::insertAll($order);
        }
        echo '运行后内存：' . round(memory_get_usage() / 1024 / 1024, 2) . 'MB' . "\r\n";
    }


    public function makeProjects()
    {
        set_time_limit(0);
        echo '运行前内存：' . round(memory_get_usage() / 1024 / 1024, 2) . 'MB' . "\r\n";
        $day = $this->request->request('day', 1);
        $model = new Projects;
//        Db::execute('TRUNCATE TABLE fa_withdraw_order');
        $start_time = $model->order('create_time')->limit(1)->value('create_time');
        if (!$start_time) {
            $start_time = time();
        }
        // 多少天
        for ($d = 0; $d < $day; $d++) {
            $time = strtotime("-{$d} day", $start_time);
            // 每天多少人
            $un = random_int(10, 1000);
            $order = [];
            for ($u = 1; $u <= $un; $u++) {
                // 每人多少笔
                $on = random_int(1, 100);
                for ($o = 0; $o < $on; $o++) {
                    $order[] = [
                        'user_id'     => $u,
                        'create_time' => $time,
                    ];
                }
            }
            Projects::insertAll($order);
        }
        echo '运行后内存：' . round(memory_get_usage() / 1024 / 1024, 2) . 'MB' . "\r\n";
    }

    public function makeRechargeOrder()
    {
        set_time_limit(0);
        echo '运行前内存：' . round(memory_get_usage() / 1024 / 1024, 2) . 'MB' . "\r\n";
        $day = $this->request->request('day', 1);
        $model = new RechargeOrder;
//        Db::execute('TRUNCATE TABLE fa_recharge_order');
        $start_time = $model->order('create_time')->limit(1)->value('create_time');
        if (!$start_time) {
            $start_time = time();
        }
        $status = $model->getCurrentTableFieldConfig('status.value_to_names');
        // 多少天
        for ($d = 0; $d < $day; $d++) {
            $time = strtotime("-{$d} day", $start_time);
            // 每天多少人
            $un = random_int(10, 1000);
            $order = [];
            for ($u = 1; $u <= $un; $u++) {
                // 每人多少笔
                $on = random_int(1, 100);
                for ($o = 0; $o < $on; $o++) {
                    $s = array_rand([0, 5]);
                    $order[] = [
                        'user_id'         => $u,
                        'amount'          => random_int(100, 10000),
                        'completion_time' => $time,
                        'status'          => array_rand($status)
                    ];
                }
            }
            RechargeOrder::insertAll($order);
        }
        echo '运行后内存：' . round(memory_get_usage() / 1024 / 1024, 2) . 'MB' . "\r\n";
    }

    public function makeRegiserUser()
    {
        echo '运行前内存：' . round(memory_get_usage() / 1024 / 1024, 2) . 'MB' . "\r\n";
        $day = $this->request->request('day', 1);
        $model = new User;
//        Db::execute('TRUNCATE TABLE fa_user');
        $start_time = $model->order('jointime')->limit(1)->value('jointime');
        if (!$start_time) {
            $start_time = time();
        }
        for ($d = 0; $d < $day; $d++) {
            $time = strtotime("-{$d} day", $start_time);
            $rand = random_int(10, 1000);
            $user = [];
            for ($u = 1; $u <= $rand; $u++) {
                $user[] = [
                    'username'        => Random::alnum(6) . $d . $u,
                    'createtime'      => $time,
                    'financial_money' => 0,
                    'jointime'        => $time,
                ];
            }
            User::insertAll($user);
        }
        echo '运行后内存：' . round(memory_get_usage() / 1024 / 1024, 2) . 'MB' . "\r\n";
    }

    public function makeUserStatistics()
    {
        echo '运行前内存：' . round(memory_get_usage() / 1024 / 1024, 2) . 'MB' . "\r\n";
        $day = $this->request->request('day', 1);
        Db::execute('TRUNCATE TABLE fa_user_statistics');
        $keys = [
            'wager_points'           => 'wager',
            'wager_totalprice'       => 'wager',
            'wager_fee1'             => 'wager_fee',
            'wager_fee2'             => 'wager_fee',
            'register'               => 'register',
            'create_red_envelopes'   => 'red_envelopes',
            'recovery_red_envelopes' => 'red_envelopes',
            'open_red_envelopes'     => 'bonus',
            'mission_bonus'          => 'bonus',
            'withdraw_amount'        => 'withdraw',
            'withdraw_fee'           => 'withdraw',
            'payment_amount'         => 'payment',
            'first_payment'          => 'payment',
            'finance_interest'       => 'interest'
        ];
        $list = [];
        for ($d = 0; $d < $day; $d++) {
            $belongdate = date('Y-m-d', strtotime("-{$d} day"));
            foreach ($keys as $name => $category) {
                $total = 0;
                for ($t = 0; $t < 24; $t++) {
                    $rand = random_int(100, 10000);
                    $item["h" . sprintf('%02s', $t)] = $rand;
                    $total += $rand;
                }
                $item['total'] = $total;
                $item['name'] = $name;
                $item['category'] = $category;
                $item['belongdate'] = $belongdate;
                $list[] = $item;
            }
        }
        UserStatistics::insertAll($list);
        echo '运行后内存：' . round(memory_get_usage() / 1024 / 1024, 2) . 'MB' . "\r\n";
    }
}