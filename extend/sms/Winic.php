<?php

namespace sms;

use think\Exception;

class Winic
{
    protected $uid = 'hotlove';

    protected $pwd = 'a123123';

    public function getGateway($type = 'international')
    {
        $urls = [
            'international' => 'http://service2.winic.org/service.asmx/SendInternationalMessages'
        ];
        return $urls[$type];
    }

    public function sendInternationalMessages($tos, $msg, $otime = '')
    {
        $params = [
            'uid'   => $this->uid,
            'pwd'   => $this->pwd,
            'tos'   => $tos,
            'msg'   => $msg,
            'otime' => $otime
        ];
        $gateway = $this->getGateway('international');
        return $this->request($gateway, $params);
    }

    protected function request($gateway, $params, $type = 'get')
    {
        try {
            $response = \fast\Http::$type($gateway, $params);
            $result = self::parse($response);
            if (!$result["#text"]) {
                throw new \Exception('sms fail');
            }
            if (!preg_match('/^\d{16,}$/', $result["#text"])) {
                throw new \Exception($result["#text"]);
            }
            return ['status' => 1, 'code' => $result["#text"]];
        } catch (\Exception $e) {
            return ['status' => 0, 'msg' => $e->getMessage()];
        }
    }

    /**
     * @param $xml
     * @return array|mixed|null
     */
    protected static function parse($xml)
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

    protected static function toArray($node)
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

    protected static function sanitize($xml)
    {
        return preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]+/u', '', $xml);
    }
}