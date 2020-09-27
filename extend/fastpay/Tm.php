<?php


namespace fastpay;


class Tm
{
    protected $type = '';

    public function payin($orderInfo)
    {
        $this->type = 'payin';
        $merchant_config = $orderInfo['merchant_config'];
        $other_params = $orderInfo['other_params'];
        $params = [
            'version'    => '1.0',            // 版本号		varchar(5)	是	默认1.0
            'customerid' => $merchant_config['mch_id'],         // 商户编号		int(8)	是	商户后台获取
            'sdorderno'  => $orderInfo['trade_no'],          // 商户订单号		varchar(20)	是	可用时间戳加随机数，不要超过18位
            'total_fee'  => $orderInfo['amount'],          // 订单金额		decimal(10,2)	是	精确到小数点后两位，例如10.24
            'paytype'    => $merchant_config['channel']['channel_value'],            // 支付编号		varchar(10)	是	详见银行编码、、或者非银行编码
            'notifyurl'  => $this->getNotifyUrl(),          // 异步通知URL		varchar(50	是	不能带有任何参数
            'returnurl'  => $this->getCallbackUrl(),          // 同步跳转URL		varchar(50)	是	不能带有任何参数
            'is_qrcode'  => 3,          // 二维码_URL地址		tinyint(1)	否	is_qrcode=3 支付地址 is_qrcode=2 支付二维码（部分不支持） is_qrcode=1 云捷处理后的地址（部分不支持）
        ];

        if (!empty($other_params['bank'])) {
            $params["bankcode"] = $other_params['bank']['bank_code'];
        }
        $params['sign'] = $this->makeSign($params, $merchant_config['private_secret']);
        return ['gateway' => $this->getPayUrl(), 'params' => $params];
    }
    public function getPayUrl()
    {
        return 'http://tmpay.pw/check/';
    }

    public function getCallbackUrl($domain = '')
    {
        return 'http://aa.cc';
    }

    public function getNotifyUrl($domain = '')
    {
        return 'http://aa.cc';
    }

    public function checkSign($params, $sign, $secret)
    {
        // TODO: Implement checkSign() method.
    }

    public function makeSign($params, $secret)
    {
        return md5(self::parseSignStr($params) . $secret);
    }

    /**
     * 解析签名字符串
     * @param $params
     * @return string
     */
    public static function parseSignStr($params): string
    {
        $sortKey = ['version', 'customerid', 'total_fee', 'sdorderno', 'notifyurl', 'returnurl'];
        $string = '';
        foreach ($sortKey as $key) {
            $string .= trim($key) . "=" . trim($params[$key]) . '&';
        }
        return $string;
    }

    public function buildParams($params)
    {
        $merchant_config = json_decode($params['merchant_config'], true);
        $data = [
            'version'    => $params['version'],            // 版本号		varchar(5)	是	默认1.0
            'customerid' => $merchant_config['customerid'],         // 商户编号		int(8)	是	商户后台获取
            'sdorderno'  => $params['trade_no'],          // 商户订单号		varchar(20)	是	可用时间戳加随机数，不要超过18位
            'total_fee'  => $params['amount'],          // 订单金额		decimal(10,2)	是	精确到小数点后两位，例如10.24
            'paytype'    => 'alipay',            // 支付编号		varchar(10)	是	详见银行编码、、或者非银行编码
//            'bankcode'    => 'CDB',            // 支付编号		varchar(10)	是	详见银行编码、、或者非银行编码
            'notifyurl'  => $this->getNotifyUrl(),          // 异步通知URL		varchar(50	是	不能带有任何参数
            'returnurl'  => $this->getCallbackUrl(),          // 同步跳转URL		varchar(50)	是	不能带有任何参数
            'is_qrcode'  => 3,          // 二维码_URL地址		tinyint(1)	否	is_qrcode=3 支付地址 is_qrcode=2 支付二维码（部分不支持） is_qrcode=1 云捷处理后的地址（部分不支持）
        ];

        $data['sign'] = $this->makeSign($data, $merchant_config['secret']);
        return $data;
    }

}