<?php


namespace app\api\controller\fastpay;


use app\common\model\UserStatistics;
use think\Controller;
use think\Log;

class Zow extends Controller
{
    public function notify()
    {
        $data = [
            'post'  => $this->request->post(),
            'param' => $this->request->param(),
            'get'   => $this->request->get(),
            'input' => file_get_contents('php://input')
        ];
        Log::write('Zow payment notify');
        Log::write($data);
        if ($data === true) {
            // 统计数据
            UserStatistics::push('payment', 12.23);
        }
    }

    public function withdarw()
    {

        $data = [
            'post'  => $this->request->post(),
            'param' => $this->request->param(),
            'get'   => $this->request->get(),
            'input' => file_get_contents('php://input')
        ];
        Log::write('Zow payment notify');
        Log::write($data);
        if ($data === true) {
            // 统计数据
            UserStatistics::push('withdarw', 98.00);
            UserStatistics::push('withdarw_fee', 2.00, 'withdarw');
        }
    }
}