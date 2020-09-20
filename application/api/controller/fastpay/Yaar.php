<?php


namespace app\api\controller\fastpay;


use think\Controller;
use think\Log;

class Yaar extends Controller
{
    public function notify()
    {
        $data = [
            'post'    => $this->request->post(),
            'get'     => $this->request->get(),
            'request' => $this->request->request(),
            'param'   => $this->request->param(),
            'input'   => file_get_contents('php://input')
        ];
        Log::write($data);
    }
}