<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\admin\model\RedEnvelopes;
use app\api\model\WithdrawOrder;
use app\common\controller\Api;
use app\common\model\IssueSales;
use app\common\model\Test;
use app\common\model\UserMission;
use app\common\model\UserMissionLog;
use app\common\model\YuEBaoOrder;
use app\common\model\YuEBaoProducts;
use Endroid\QrCode\QrCode;
use fast\Http;
use fast\Random;
use Firebase\JWT\JWT;
use gmars\nestedsets\NestedSets;
use think\Config;
use think\Db;
use think\exception\DbException;
use think\Hook;
use think\Request;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['index', 'agreement', 'notify', 'test'];
    protected $noNeedRight = ['*'];

    public function init()
    {
        $site = Config::get('site');
        $allow_keys = ['min_withdraw_amount', 'name', 'team_fees', 'wager_rate', 'withdraw_rate'];
        $this->success('', array_intersect_key($site, array_flip($allow_keys)));
    }

    public function test()
    {
        $product = YuEBaoProducts::get(3);
        if(!$product){
            exit('product is empty');
        }
        $result = YuEBaoOrder::transferIn($product, 1, 100000);
        dump($result->toArray());
    }


    /**
     * 首页
     * @param int $ids
     * @throws DbException
     */
    public function index($ids = 6)
    {
        $this->success();
        $row = WithdrawOrder::get($ids);
        if (!$row) {
            $this->error('订单不存在');
        }
        if ($this->request->isPost()) {
            $url = 'https://www.zowpay.com/Payment/Dfpay/add.do';
            $mchid = '200409159';
            $secret = 'wwg5batjlj8yb3wby6gw41ktjxqgvwnl';
            $params = [
                'mchid'        => $mchid, //	商户号
                'out_trade_no' => \NumberPool::getOne(), //	订单号
                'money'        => $row->real_amount, //	金额
                'notifyurl'    => url('index/notify'), //	回调地址
                'cnapscode'    => 123, //	印度税卡
                'bankname'     => '32', //	银行名称
                'subbranch'    => '33', //	结算银行IFSC
                'accountname'  => '31', //	开户名
                'cardnumber'   => '3213', //	银行卡号
                'idcard'       => '3213', //	印度身份证号
                'mobile'       => '1321', //	手机号
                'accounttype'  => '321', //	账户类型
                'userip'       => $this->request->ip(), //	请求Ip
            ];

            $native = ["mchid", "out_trade_no", "money", "bankname", "accountname", "cardnumber"];
            ksort($params);
            $signStr = "";
            foreach ($params as $key => $val) {
                if (in_array($key, $native, true)) {
                    $signStr .= $key . "=" . $val . "&";
                }
            }
            rtrim($signStr, '&');
            $params['pay_md5sign'] = strtoupper(md5($signStr . "&key=" . $secret));

            $response = Http::post($url, $params);
            $this->success('', $response);
        }
        $this->error('不允许');
    }

    public function notify()
    {

    }

    /**
     * 注册协议
     * @param string $name
     * @throws DbException
     */
    public function agreement($name = '')
    {
        if ($name) {
            $names = explode(',', $name);
            $articles = Article::all(['name' => ['in', $names], 'status' => 'normal']);
            foreach ($articles as $article) {
                if ($article->need_login && !$this->auth->isLogin()) {
                    $this->error('需要登录', '', 401);
                }
            }
            if (count($names) === 1) {
                $articles = $articles[0];
            }
            $this->success('', $articles);
        }
        $this->error();
    }
}
