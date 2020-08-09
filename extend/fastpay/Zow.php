<?php


namespace fastpay;


class Zow extends Base
{
    public function getPayUrl()
    {
        return "https://www.zowpay.com/index.php/Pay/Index.do";   //提交地址
    }

    public function getCallbackUrl($domain = '')
    {
        $scheme = parse_url($domain, PHP_URL_SCHEME);
        $scheme || $scheme = 'http://';
        $url = url('fastpay/zow/callback', '', '', !!$domain);
        return $domain ? ($scheme . $domain . $url) : $url;
    }

    public function getNotifyUrl($domain = '')
    {
        $scheme = parse_url($domain, PHP_URL_SCHEME);
        $scheme || $scheme = 'http://';
        $url = url('fastpay/zow/notify');
        return $domain ? ($scheme . $domain . $url) : $url;
    }

    public function checkSign($params, $sign, $secret)
    {
        // TODO: Implement checkSign() method.
    }

    public function makeSign($params, $secret)
    {
        return strtoupper(md5(self::parseSignStr($params) . "&key=" . $secret));
    }

    /**
     * 解析签名字符串
     * @param $params
     * @return string
     */
    public static function parseSignStr($params): string
    {
        $native = ["pay_memberid", "pay_orderid", "pay_amount", "pay_applydate", "pay_notifyurl", "pay_callbackurl"];
        ksort($params);
        reset($params);
        $signStr = "";
        foreach ($params as $key => $val) {
            if (in_array($key, $native, true)) {
                $signStr .= $key . "=" . $val . "&";
            }
        }
        return rtrim($signStr, '&');
    }

    public function buildParams($params)
    {
        $merchant_config = json_decode($params['merchant_config'], true);
        $native = array(
            "pay_memberid"    => $merchant_config['merchant_id'],   //商户后台API管理获取
            "pay_orderid"     => $params['trade_no'],    //订单号
            "pay_amount"      => bcmul($params['amount'], 1, 2),    //交易金额
            "pay_applydate"   => time(),
            "pay_notifyurl"   => $this->getNotifyUrl(),
            "pay_callbackurl" => $this->getCallbackUrl(),
        );
        $native['pay_country'] = 'IN';
        $native['pay_currency'] = 'INR';
        $native['pay_productname'] = $params['product_title'];
        $native["pay_bankcode"] = "924";  //商户后台通道费率页 获取银行编码
        $native["pay_md5sign"] = $this->makeSign($native, $merchant_config['secret']);
        return $native;
    }

}