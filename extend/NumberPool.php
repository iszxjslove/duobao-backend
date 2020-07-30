<?php


/**
 * 号码池
 * Class NumberPool
 */
class NumberPool
{
    /**
     * @var NumberPool
     */
    private static $_instance;

    /**
     * @var array
     */
    private $options = [
        'host'       => '127.0.0.1',
        'port'       => 6379,
        'password'   => '',
        'select'     => 0,                      // 选择数据库
        'timeout'    => 0,                      // 超时
        'persistent' => false,                  // 长连接
        'prefix'     => 'redisNumberPool',     // 前缀
    ];

    /**
     * @var int 最小补充量
     */
    private $min_replenish_amount = 1000;

    /**
     * @var int 周期存量时间(秒)
     */
    public $period_time = 120;

    /**
     * @var \Redis
     */
    private $handler;

    /**
     * @var int 机器ID
     */
    private $worker_id = 0;

    /**
     * @var int 数据中心ID
     */
    private $data_center_id = 0;

    /**
     * 构造函数
     * NumberPool constructor.
     * @param array $options
     */
    private function __construct($options = [])
    {
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('not support: redis');
        }
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $this->handler = new \Redis;
        if ($this->options['persistent']) {
            $this->handler->pconnect($this->options['host'], $this->options['port'], $this->options['timeout'], 'persistent_id_' . $this->options['select']);
        } else {
            $this->handler->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
        }

        if ('' != $this->options['password']) {
            $this->handler->auth($this->options['password']);
        }

        if (0 != $this->options['select']) {
            $this->handler->select($this->options['select']);
        }
        return $this;
    }

    private function __clone(){}

    /**
     * 单例
     * @param array $options
     * @return NumberPool
     */
    public static function getInstance($options =[])
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 切换数据库
     * @param int $db
     * @return NumberPool
     */
    public static function db($db = 0)
    {
        $self = self::getInstance();
        $self->handler->select($db);
        return $self;
    }

    /**
     * 切换机器码
     * @param $id
     * @return $this
     */
    public static function worker($id)
    {
        $self = self::getInstance();
        $self->worker_id = $id;
        return $self;
    }

    /**
     * 切换数据中心
     * @param $id
     * @return $this
     */
    public static function center($id)
    {
        $self = self::getInstance();
        $self->data_center_id = $id;
        return $self;
    }

    /**
     * 单个获取
     * @return bool|mixed
     * @throws Exception
     */
    public static function getOne()
    {
        $self = self::getInstance();
        $number = $self->handler->lPop($self->getRealKey('list'));
        if (!$number) {
            $self->replenish();
            $number = $self->handler->lPop($self->getRealKey('list'));
        }
        return $number;
    }

    /**
     * 多个获取
     * @param $num
     * @return Generator
     * @throws Exception
     */
    public function getMultiple($num)
    {
        while ($num > 0) {
            yield $this->getOne();
            $num--;
        }
    }

    /**
     * 清除所有
     */
    public function flush()
    {
        $keys = $this->handler->keys($this->getRealKey('*'));
        if($keys){
            foreach ($keys as $key) {
                $this->handler->del($key);
            }
        }
    }

    /**
     * 格式化数据中心ID 2位
     * @return string
     */
    private function formatDataCenterId()
    {
        return sprintf('%02s', $this->data_center_id);
    }

    /**
     * 格式化机器ID 3位
     * @return string
     */
    private function formatWorkerId()
    {
        return sprintf('%03s', $this->worker_id);
    }

    /**
     * 格式化序列号 4位
     * @param $sequence
     * @return string
     */
    private function formatSequenceId($sequence)
    {
        return sprintf('%04s', $sequence);
    }

    /**
     * @return int
     */
    private function getLastInventory()
    {
        return $this->handler->hGet($this->getRealKey('table'), 'last_inventory');
    }

    /**
     * @return int
     */
    private function getLastReplenishTime()
    {
        return $this->handler->hGet($this->getRealKey('table'), 'last_replenish_time');
    }

    /**
     * @return int
     */
    private function getLastReplenishAmount()
    {
        return $this->handler->hGet($this->getRealKey('table'), 'last_replenish_amount');
    }

    /**
     * 获取调整补充量
     * @return int
     * @throws Exception
     */
    private function getReplenishAmount()
    {
        // 没有补充过或不自动调整
        $lastTimestamp = $this->getLastReplenishTime();
        if (!$lastTimestamp) {
            return $this->min_replenish_amount;
        }
        $timestamp = $this->microTime();
        //如果当前时间小于上一次ID生成的时间戳，说明系统时钟回退过这个时候应当抛出异常
        if ($timestamp < $lastTimestamp) {
            throw new Exception(sprintf("时钟向后移动。拒绝生成id达%d毫秒", $lastTimestamp - $timestamp));
        }
        // 根据上次补充调整补充量
        $diff_time = $timestamp - $lastTimestamp;
        // 消耗
        $consume = $this->getLastInventory() - $this->count();
        $amount = ceil($consume / $diff_time * $this->period_time * 1000);
        return $amount < $this->min_replenish_amount ? $this->min_replenish_amount : $amount;
    }

    /**
     * 统计剩余量
     * @return int
     */
    private function count()
    {
        return $this->handler->lLen($this->getRealKey('list'));
    }

    /**
     * @param int $num
     * @throws Exception
     */
    private function replenish($num = 0)
    {
        $num = $num ?: $this->getReplenishAmount();
        $scale = 10000;
        $times = (int)($num / $scale);
        $margin = $num % $scale;

        $this->handler->hSet($this->getRealKey('table'), 'last_inventory', $this->count());
        $this->handler->hSet($this->getRealKey('table'), 'last_replenish_amount', 0);

        if ($times > 0) {
            for ($i = 0; $i < $times; $i++) {
                $this->multiplePushOrder($scale);
            }
        }
        if ($margin > 0) {
            $this->multiplePushOrder($margin);
        }

        $this->handler->hIncrBy($this->getRealKey('table'), 'last_inventory', $num);
        $this->handler->hIncrBy($this->getRealKey('table'), 'last_replenish_amount', $num);
        $this->handler->hSet($this->getRealKey('table'), 'last_replenish_time', $this->microTime());
    }

    /**
     * @param $num
     * @throws Exception
     */
    private function multiplePushOrder($num)
    {
        $list = [];
        $generator = $this->makeNumber($num);
        foreach ($generator as $order) {
            $list[] = $order;
        }
        $this->pushOrder($list);
        unset($list);
    }

    /**
     * 补充号码
     * @param int $num
     * @return Generator
     * @throws Exception
     */
    private function makeNumber($num = 0)
    {
        # 毫秒时间戳(13)-数据中心(2)-机器码(3)-序列号(4)
        # 1588128483000-55-666-0000
        $sequence = 0;
        $sequenceMask = -1 ^ (-1 << 13);
        $lastTimestamp = $this->getLastReplenishTime();
        while ($num > 0) {
            $timestamp = $this->microTime();
            //如果是同一时间生成的，则进行毫秒内序列
            if ($lastTimestamp == $timestamp) {
                $sequence = ($sequence + 1) & $sequenceMask;
                //毫秒内序列溢出
                if ($sequence == 0) {
                    //阻塞到下一个毫秒,获得新的时间戳
                    $timestamp = $this->tilNextMillis($lastTimestamp);
                }
            } //时间戳改变，毫秒内序列重置
            else {
                $sequence = 0;
            }
            $lastTimestamp = $timestamp;
            yield implode('', [$timestamp, $this->formatDataCenterId(), $this->formatWorkerId(), $this->formatSequenceId($sequence)]);
            $num--;
        }
    }

    /**
     * @param $order
     */
    private function pushOrder($order)
    {
        $order = (array)$order;
        shuffle($order);
        if (array_rand([0, 1])) {
            $this->handler->rPush($this->getRealKey('list'), ...$order);
        } else {
            $this->handler->lPush($this->getRealKey('list'), ...$order);
        }
    }

    /**
     * 阻塞到下一个毫秒，直到获得新的时间戳
     * @param int $lastTimestamp 上次生成ID的时间截
     * @return int 当前时间戳
     */
    private function tilNextMillis($lastTimestamp)
    {
        $timestamp = $this->microTime();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->microTime();
        }
        return $timestamp;
    }

    /**
     * 返回以毫秒为单位的当前时间
     * @return int 当前时间(毫秒)
     */
    private function microTime()
    {
        return substr(str_replace(".", "", microtime(true)), 0, 13);
    }

    /**
     * 获取真实的Key
     * @param $name
     * @return string
     */
    private function getRealKey($name)
    {
        return implode('_', [$this->options['prefix'], $this->formatDataCenterId(), $this->formatWorkerId(), $name]);
    }
}