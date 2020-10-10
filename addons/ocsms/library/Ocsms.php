<?php

namespace addons\ocsms\library;


class Ocsms
{
    protected static $instance = null;

    protected $config = [];

    protected $error = '';

    private $params = [];

    public function __construct($options = [])
    {
        if ($config = get_addon_config('ocsms')) {
            $this->config = array_merge($this->config, $config);
        }
        $this->config = array_merge($this->config, is_array($options) ? $options : []);
    }

    /**
     * 单例
     * @param array $options
     * @return static|null
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    /**
     * 接收手机
     * @param  $mobile
     * @return $this
     */
    public function mobile($mobile): self
    {
        $tmp = [];
        foreach ((array)$mobile as $item) {
            $tmp[] = '91' . $item;
        }
        $this->params['contacts'] = implode(',', $tmp);
        return $this;
    }

    /**
     * 短信内容
     * @param string $msg
     * @return $this
     */
    public function msg($msg = ''): self
    {
        $this->params['msg'] = $this->config['sign'] . $msg;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return array_merge([
            'key' => $this->config['key'],
            'senderid' => $this->config['sender_id'],
        ], $this->params);
    }

    public function send()
    {
        $params = $this->getParams();
        $postArr = array(
            'key'      => $params['key'],
            'campaign' => 53,
            'routeid'  => 28,
            'senderid' => $params['senderid'],
            'type'     => 'text',
            'msg'      => $params['msg'],
            'contacts' => $params['contacts']
        );
        return self::request($this->getGateway(), $postArr);
    }

    private static function request($gateway, $params, $type = 'get')
    {
        $response = \fast\Http::$type($gateway, $params);
        $response = trim($response);
        if ($response) {
            $response =  json_decode($response,true);
            if (strtolower($response['result']) === 'success') {
                return true;
            }
        }
        return false;
    }

    public function getGateway()
    {
        return 'https://ocsms.in/smsapi/index';
    }
}