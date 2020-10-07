<?php


namespace app\common\library;


use app\common\model\YuEBaoOrder;
use app\common\model\YuEBaoProducts;

/**
 * Class YuEBao
 * @package app\common\library
 */
class YuEBao
{
    /**
     * @var YuEBaoProducts
     */
    protected $product;

    /**
     * @var YuEBaoOrder
     */
    protected $order;

    /**
     * @param YuEBaoProducts $product
     */
    public function setProduct(YuEBaoProducts $product)
    {
        $this->product = $product;
    }

    public function getNextInterestTime($current = 0)
    {
        $current || $current = time();
        $param = $this->order ?? $this->product;
        $time = 0;
        switch ($param->interest_method) {
            case 'fixed':
                $time = strtotime("+{$param->period} {$param->period_unit}", $current);
                break;
            case 'day':
                $time = $current + 86400;
                break;
        }
        return $time;
    }
}