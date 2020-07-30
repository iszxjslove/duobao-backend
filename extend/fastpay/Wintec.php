<?php

namespace fastpay;


use think\Request;

class Wintec extends Base
{
    private $currency = [
//  AED  AE	United Arab Emirates	阿联酋	AED
//  ARS  AR	Argentina	阿根廷	ARS
//  AUD  AU	Australia	澳大利亚	AUD
//  BBD  BB	Barbados	巴巴多斯	BBD
//  BHD  BH	Bahrain	巴林	BHD
//  BIF  BI	Burundi	布隆迪	BIF
//  BMD  BM	Bermuda	百慕大	BMD
//  BOB  BO	Bolivia	玻利维亚	BOB
//  BZD  BZ	Belize	伯利兹	BZD
//  CAD  CA	Canada	加拿大	CAD
//  CHF  CH	Switzerland	瑞士	CHF
//  CNY  CN	China	中国 内地	CNY
//  CRC  CR	Costa Rica	哥斯达黎加	CRC
//  CZK  CZ	Czech Republic	捷克	CZK
//  DJF  DJ	Djibouti	吉布提	DJF
//  DKK  DK	Denmark	丹麦	DKK
//  DOP  DO	Dominican Republic	多米尼加	DOP
//  EGP  EG	Egypt	埃及	EGP
//  GBP  GB	Great Britain (United Kingdom; England)	英国	GBP
//  GNF  GN	Guinea	几内亚	GNF
//  GTQ  GT	Guatemala	危地马拉	GTQ
//  HKD  HK	Hong Kong	香港	HKD
//  HNL  HN	Honduras	洪都拉斯	HNL
//  HRK  HR	Croatia	克罗地亚	HRK
//  HUF  HU	Hungary	匈牙利	HUF
//  IDR  ID	Indonesia	印尼	IDR
//  ILS  IL	Israel	以色列	ILS
//  INR  IN	India	印度	INR
//  ISK  IS	Iceland	冰岛	ISK
//  JMD  JM	Jamaica	牙买加	JMD
//  JPY  JP	Japan	日本	JPY
//  KRW  KR	South Korea	韩国 南朝鲜	KRW
//  KWD  KW	Kuwait	科威特	KWD
//  KZT  KZ	Kazakhstan	哈萨克斯坦	KZT
//  LKR  LK	Sri Lanka	斯里兰卡	LKR
//  LTL  LT	Lithuania	立陶宛	LTL
//  MAD  MA	Morocco	摩洛哥	MAD
//  MOP  MO	Macao	澳门	MOP
//  MXN  MX	Mexico	墨西哥	MXN
//  MYR  MY	Malaysia	马来西亚	MYR
//  NOK  NO	Norway	挪威	NOK
//  NZD  NZ	New Zealand	新西兰	NZD
//  PKR  PK	Pakistan	巴基斯坦	PKR
//  PYG  PY	Paraguay	巴拉圭	PYG
//  QAR  QA	Qatar	卡塔尔	QAR
//  RUB  RU	Russian Federation	俄罗斯	RUB
//  SAR  SA	Saudi Arabia	沙特阿拉伯	SAR
//  SEK  SE	Sweden	瑞典	SEK
//  SGD  SG	Singapore	新加坡	SGD
//  TND  TN	Tunisia	突尼斯	TND
//  THB  TH	Thailand	泰国	THB
//  UGX  UG	Uganda	乌干达	UGX
//  USD  US	United States of America (USA)	美国	USD
//  VND  VN	Vietnam	越南	VND
//  VUV  VU	Vanuatu	瓦努阿图	VUV
//  ZAR  ZA	South Africa	南非	ZAR
//  PHP  PH	The Philippines	菲律宾	PHP
    ];

    public function getPayUrl()
    {
        # $production = 'http://api.wintecpay.com/winapi/clientapi/unifiedorder';
        return 'http://shop.ling-chuang.com:18099/winapi/clientapi/unifiedorder';
    }

    public function getCallbackUrl($domain = '')
    {
        $scheme = parse_url($domain, PHP_URL_SCHEME);
        $scheme || $scheme = 'http://';
        $url = url('/api/fastpay/wintec/callback', '', '', !!$domain);
        return $domain ? ($scheme . $domain . $url) : $url;
    }

    public function getNotifyUrl($domain = '')
    {
        $scheme = parse_url($domain, PHP_URL_SCHEME);
        $scheme || $scheme = 'http://';
        $url = url('/api/fastpay/wintec/notify', '', '', !!$domain);
        return $domain ? ($scheme . $domain . $url) : $url;
    }

    /**
     * 校验签名
     * @param $params
     * @param $sign
     * @param $secret
     * @return bool
     */
    public function checkSign($params, $sign, $secret)
    {
        return $sign === $this->makeSign($params, $secret);
    }

    /**
     * 生成签名
     * @param $params
     * @param $secret
     * @return string
     */
    public function makeSign($params, $secret)
    {
        return hash('sha256', self::parseSignStr($params) . "&key={$secret}");
    }

    /**
     * 解析签名字符串
     * @param $params
     * @return string
     */
    public static function parseSignStr($params): string
    {
        ksort($params);
        $string = '';
        foreach ($params as $key => $value) {
            if ($value === '' || $key === 'sign') {
                continue;
            }
            if ($key === 'extInfo') {
                $value = self::parseSignStr($value);
            }
            $string .= "{$key}={$value}&";
        }
        return rtrim($string, '&');
    }

    public function buildParams($orderInfo)
    {
        $params = [
            'version'    => '1.1',
            'appId'      => $orderInfo['appid'],
            'country'    => 'IN',
            'currency'   => 'INR',
            'merTransNo' => $orderInfo['trade_no'],
            'notifyUrl'  => 'http://yoursite.com/notifyurl',
            'prodName'   => $orderInfo['product_title'],
            'returnUrl'  => 'http://yoursite.com/notifyurl',
            'amount'     => $orderInfo['amount'],
        ];
        $params['sign'] = $this->makeSign($params, $orderInfo['secret']);
        return $params;
    }

}