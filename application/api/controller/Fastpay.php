<?php


namespace app\api\controller;


use app\common\controller\Api;
use app\common\model\RechargeOrder;
use think\Config;
use think\Db;
use think\Hook;
use think\Loader;
use think\Log;
use think\View;

/**
 * Class Fastpay
 * @package app\api\controller
 */
abstract class Fastpay extends Api
{

    /**
     * @var RechargeOrder
     */
    protected $order;

    /**
     * @var RechargeOrder
     */
    protected $model;

    /**
     * @var View 视图类实例
     */
    protected $view;

    /**
     * 布局模板
     * @var string
     */
    protected $layout = 'quasar';

    /**
     * @var string
     */
    protected $noNeedLogin = 'notify,callback';

    /**
     * @var string
     */
    protected $noNeedRight = '*';

    /**
     * @var string
     */
    protected $notifyRequestMethod = 'param';

    protected $notifyReturnMsg = 'ok';

    /**
     * @var string 订单号
     */
    protected $trade_no = '';

    /**
     * @var string 错误信息
     */
    private $_error = '';

    protected function _initialize()
    {
        if (!is_array($this->noNeedLogin)) {
            $this->noNeedLogin = explode(',', $this->noNeedLogin);
        }
        if (!in_array('notify', $this->noNeedLogin, true)) {
            $this->noNeedLogin[] = 'notify';
        }
        parent::_initialize();
        $this->view = View::instance(Config::get('template'), Config::get('view_replace_str'));
        // 如果有使用模板布局
        if ($this->layout) {
            $this->view->engine->layout('layout/' . $this->layout);
        }
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return $this
     */
    protected function assign($name, $value = ''): self
    {
        $this->view->assign($name, $value);
        return $this;
    }

    /**
     * @return string
     * @throws \think\Exception
     */
    public function payin()
    {
        $orderInfo = $this->getOrder()->toArray();
        $params = $this->buildPayParams($orderInfo);
        $this->assign('payurl', $this->getPayUrl());
        $this->assign('params', $params);
        return $this->view->fetch('payview/index');
    }

    final public function notify()
    {
        $method = $this->notifyRequestMethod;
        $params = $this->request->$method();
        Db::startTrans();
        try {
            if (!$params) {
                $this->setError('参数错误');
                throw new \Exception('fail');
            }
            $this->getNotifyOrder($params);
            if (!$this->order) {
                $this->setError('订单不存在');
                throw new \Exception('fail');
            }
            if ($this->order->status !== 0) {
                $this->setError('订单不需要处理');
                throw new \Exception($this->notifyReturnMsg);
            }
            if (!$amount = $this->handleNotify($params, $this->order)) {
                throw new \Exception('fail');
            }
            if ((float)$amount !== (float)$this->order->amount) {
                $this->setError("支付金额不对[notify: {$amount},order: {$this->order->amount}]");
                throw new \Exception('fail');
            }
            $this->order->amount = $amount;
            $this->order->status = 1;
            $this->order->save();
            \app\common\model\User::money($this->order->user_id, $amount, 'recharge');
            Db::commit();
            // 充值成功后
            $user = \app\common\model\User::get($this->order->user_id);
            Hook::listen("recharge_after", $user, $this->order);
            die($this->notifyReturnMsg);
        } catch (\Exception $e) {
            Db::rollback();
            if ($params) {
                Log::info(json_encode($params));
            }
            if ($this->order) {
                Log::info(json_encode(collection((array)$this->order)->toArray()));
            }
            Log::error($this->getError());
            die($e->getMessage());
        }
    }

    public function callback()
    {
        $url = Config::get('site.frontend_url') . 'my';
        header("Location: {$url}",TRUE,301);
        exit;
    }

    public static function selectFastpay($type = ''): array
    {
        $files = glob(__DIR__ . '/fastpay/*.php');
        $list = [];
        foreach ($files as $file) {
            if (is_dir($file)) {
                continue;
            }
            $name = basename($file, '.php');
            /**
             * @var self $class
             */
            $class = '\\app\\api\\controller\\fastpay\\' . $name;
            if (method_exists($class, 'getInfo')) {
                $info = $class::getInfoValue();
                $name = $info['name'] ?? strtolower($name);
                if (!$type || !empty($info[$type])) {
                    $list[$name] = $info;
                }
            }
        }
        return $list;
    }

    /**
     * @return array
     */
    abstract protected static function getInfo(): array;

    public static function getInfoValue($name = '')
    {
        $info = static::getInfo();
        $keys = $name ? explode('.', $name) : [];
        foreach ($keys as $key) {
            $info = $info[$key] ?? '';
        }
        return $info;
    }

    /**
     * @return string
     */
    protected function getPayUrl(): string
    {
        return static::getInfoValue('gateway.unified');
    }

    /**
     * @param $orderInfo
     * @return array
     */
    abstract protected function buildPayParams($orderInfo): array;

    /**
     * @param $params
     * @param $secret
     * @return string
     */
    abstract protected function makeSign($params, $secret): string;

    /**
     * @param $params
     * @return mixed
     */
    abstract protected function handleNotify($params, $orderInfo);

    /**
     * @param $params
     * @return mixed
     */
    abstract protected function getNotifyOrder($params);

    /**
     * @return mixed
     */
    protected function parseRoutePath()
    {
        $controllerName = Loader::parseName($this->request->controller());
        return str_replace(array('fastpay', '.'), array('pay', '/'), strtolower($controllerName));
    }

    /**
     * @return string
     */
    protected function getNotifyUrl(): string
    {
        return url($this->parseRoutePath() . '/notify');
    }

    /**
     * @return string
     */
    protected function getCallbackUrl(): string
    {
        return url($this->parseRoutePath() . '/callback');
    }

    protected function setOrder($trade_no)
    {
        if (!$trade_no) {
            $trade_no = $this->request->param('trade_no');
        }
        $this->order = RechargeOrder::getByTradeNo($trade_no);
    }

    /**
     * @return RechargeOrder
     */
    protected function getOrder(): RechargeOrder
    {
        if ($this->order) {
            return $this->order;
        }
        $this->setOrder(false);
        if (!$this->order) {
            $this->error('Order does not exist');
        }
        return $this->order;
    }

    protected function setError($msg = '')
    {
        $this->_error = $msg;
    }

    public function getError(): string
    {
        return $this->_error;
    }
}