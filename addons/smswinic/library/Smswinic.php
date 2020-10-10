<?php

namespace addons\smswinic\library;

use fast\Http;

class Smswinic
{
    private $_params = [];
    protected $error = '';
    protected $config = [];
    protected static $instance = null;
    protected $statusStr = array(
        "0"   => "短信发送成功",
        "-08" => "Invalid phone number format，No '+' is required",
        "-2"  => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
        "30"  => "密码错误",
        "40"  => "账号不存在",
        "41"  => "余额不足",
        "42"  => "帐户已过期",
        "43"  => "IP地址限制",
        "50"  => "内容含有敏感词"
    );

    public function __construct($options = [])
    {
        if ($config = get_addon_config('smswinic')) {
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
     * @param string $mobile
     * @return $this
     */
    public function mobile($mobile = '')
    {
        $this->_params['mobile'] = $mobile;
        return $this;
    }

    /**
     * 短信内容
     * @param string $msg
     * @return $this
     */
    public function msg($msg = '')
    {
        $this->_params['msg'] = $this->config['sign'] . $msg;
        return $this;
    }

    /**
     * 定时发送
     * @param string $time
     * @return $this
     */
    public function otime($time = '')
    {
        $this->_params['otime'] = $time;
        return $this;
    }

    public function send()
    {
        $this->error = '';
        $params = $this->_params();
        $postArr = array(
            'uid'   => $params['uid'],
            'pwd'   => $params['pwd'],
            'tos'   => '91' . $params['mobile'],
            'msg'   => $params['msg'],
            'otime' => $params['otime'] ?? '',
        );
        $result = self::request($this->getGateway('international'), $postArr);
        if (!$result['status']) {
            $this->error = $result['msg'];
            return false;
        }
        return true;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    private function getGateway($type = '')
    {
        $urls = [
            'international' => 'http://service2.winic.org/service.asmx/SendInternationalMessages'
        ];
        return $urls[$type];
    }

    private function _params()
    {
        return array_merge([
            'uid' => $this->config['uid'],
            'pwd' => $this->config['pwd'],
        ], $this->_params);
    }

    private static function request($gateway, $params, $type = 'get')
    {
        try {
            $response = \fast\Http::$type($gateway, $params);
            $result = self::parse($response);
            if (!$result || empty($result['#text'])) {
                throw new \Exception('send fail');
            }
            if (!preg_match('/^\d{16,}$/', $result["#text"])) {
                throw new \Exception($result["#text"]);
            }
            return ['status' => 1, 'code' => $result["#text"], 'data' => $result];
        } catch (\Exception $e) {
            return ['status' => 0, 'msg' => $e->getMessage() . ':Invalid phone number'];
        }
    }

    /**
     * @param $xml
     * @return array|mixed|null
     */
    private static function parse($xml)
    {
        $xml = self::sanitize($xml);
        $result = null;
        try {
            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            $result = self::toArray($dom->documentElement);
        } catch (\Exception $e) {
            $result = $xml;
        }
        return $result;
    }

    private static function toArray($node)
    {
        $array = false;

        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                $array[$attr->nodeName] = $attr->nodeValue;
            }
        }

        if ($node->hasChildNodes()) {
            if ($node->childNodes->length === 1) {
                $array[$node->firstChild->nodeName] = self::toArray($node->firstChild);
            } else {
                foreach ($node->childNodes as $childNode) {
                    if ($childNode->nodeType !== XML_TEXT_NODE) {
                        $array[$childNode->nodeName][] = self::toArray($childNode);
                    }
                }
            }
        } else {
            return $node->nodeValue;
        }
        return $array;
    }

    private static function sanitize($xml)
    {
        return preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]+/u', '', $xml);
    }
}