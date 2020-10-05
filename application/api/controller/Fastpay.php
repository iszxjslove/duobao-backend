<?php


namespace app\api\controller;


use app\common\controller\Api;
use app\common\model\RechargeOrder;
use think\Config;
use think\Loader;
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

    /**
     *
     */
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

    /**
     *
     */
    final public function notify()
    {
        $method = $this->notifyRequestMethod;
        $params = $this->request->$method();
        try {
            if (!$params) {
                throw new \Exception('fail');
            }
            if (!$result = $this->handleNotify($params)) {
                throw new \Exception('fail 1001');
            }
            die($result);
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     *
     */
    public function callback()
    {
        echo 'ok;';
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
    abstract protected function handleNotify($params);

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

    /**
     * @return RechargeOrder
     */
    protected function getOrder(): RechargeOrder
    {
        if ($this->order) {
            return $this->order;
        }
        $this->model = new RechargeOrder;
        $trade_no = $this->request->param('trade_no');
        if ($trade_no) {
            $this->order = $this->model->getByTradeNo($trade_no);
            if (!$this->order) {
                $this->error('Order does not exist');
            }
        } else {
            $this->error('Params Error');
        }
        return $this->order;
    }
}