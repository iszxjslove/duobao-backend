<?php


namespace app\common\model;


use think\Model;

/**
 * Class YuEBaoProducts
 * @package app/common/model
 * @property int id 金融产品ID
 * @property string title 标题
 * @property string explain 描述
 * @property int period 周期
 * @property string period_unit 周期单位
 * @property float profit 收益
 * @property string profit_unit 收益单位
 * @property string interest_method 结息方式
 * @property string interest_where 利息去向
 * @property string principal_where 本金去向
 * @property string status 状态
 */
class YuEBaoProducts extends Model
{
    protected $name = 'yuebao_products';
}