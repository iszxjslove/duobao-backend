<?php


namespace app\api\controller\fastpay;


use app\api\model\WithdrawOrder;
use app\common\model\RechargeOrder;
use app\common\model\User;
use app\common\model\UserStatistics;
use think\Controller;
use think\Db;
use think\Exception;
use think\Hook;
use think\Log;

class Zow extends Controller
{
    public function notify()
    {

//        通知格式
//        'memberid' => '200825122',
//        'orderid' => '1596137374642000000059',
//        'transaction_id' => '20200809163202504948',
//        'amount' => '10.0000',
//        'datetime' => '20200809163334',
//        'returncode' => '00',
//        'sign' => '13AACB74892E38C89F3799AF790E23FE',
//        'attach' => '',
//        'userid' => '',



        $data = [
          'post' => $this->request->post(),
          'get' => $this->request->get(),
          'request' =>$this->request->request(),
          'param' => $this->request->param(),
          'input' => file_get_contents('php://input')
        ];
        Log::write($data);
    }

    public function test_notify()
    {
        $id = $this->request->request('id');
        $order = RechargeOrder::get($id);
        if ($order) {
            Db::startTrans();
            try {
                $user = User::get($order->user_id);
                $order->status = $order->getCurrentTableFieldConfig('status.success.value');
                $order->completion_time = time();
                $order->save();
                Hook::listen('recharge_after', $user, $order);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
            }
        }
    }

    public function withdarw()
    {
        $id = $this->request->request('id');
        $order = WithdrawOrder::get($id);
        if ($order) {
            Db::startTrans();
            try {
                $user = User::get($order->user_id);
                $order->status = $order->getCurrentTableFieldConfig('status.success.value');
                $order->completion_time = time();
                $order->save();
                Hook::listen('withdraw_after', $user, $order);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
            }
        }
    }
}