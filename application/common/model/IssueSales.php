<?php


namespace app\common\model;


use app\api\model\Game;
use app\api\model\Issue;
use think\Exception;
use think\Model;

/**
 * Class IssueSales
 * @package app/common/model
 * @property int id 统计ID
 * @property int issue_id 奖期ID
 * @property string issue 奖期号
 * @property float totalprice 总收入
 * @property string total_contract_amount 总合同金额
 * @property string belongdate 属于哪天的奖期
 * @property float EE0 数字0预计支出
 * @property float EE1 数字1预计支出
 * @property float EE2 数字2预计支出
 * @property float EE3 数字3预计支出
 * @property float EE4 数字4预计支出
 * @property float EE5 数字5预计支出
 * @property float EE6 数字6预计支出
 * @property float EE7 数字7预计支出
 * @property float EE8 数字8预计支出
 * @property float EE9 数字9预计支出
 * @property float total_actual_expenditure 实际支出总额
 * @property float actual_total_profit 本期利润总额
 */
class IssueSales extends Model
{
    protected $name = 'issue_sales';

    public static function push($issue_id, $selected, $totalprice ,$contract_amount=0)
    {
        $gid = 1;
        $game = Game::get($gid);
        if (!$game) {
            return false;
        }
        $issue = Issue::get($issue_id);
        if (!$issue) {
            return false;
        }
        $colors = [
            'green'  => '1|3|5|7|9',
            'red'    => '0|2|4|6|8',
            'violet' => '0|5'
        ];
        $contract_amount = $contract_amount ? : $totalprice;
        $codes = [];
        if (isset($colors[$selected])) {
            $numbers = explode('|', $colors[$selected]);
            foreach ($numbers as $number) {
                switch ($selected) {
                    case 'green':
                        if ($game->green_lucky === $number) {
                            $codes[$number] = bcmul($contract_amount, $game->green_lucky_odds, 2);
                        } else {
                            $codes[$number] = bcmul($contract_amount, $game->green_ordinary_odds, 2);
                        }
                        break;
                    case 'red':
                        if ($game->red_lucky === $number) {
                            $codes[$number] = bcmul($contract_amount, $game->red_lucky_odds, 2);
                        } else {
                            $codes[$number] = bcmul($contract_amount, $game->red_ordinary_odds, 2);
                        }
                        break;
                    case 'violet':
                        $codes[$number] = bcmul($contract_amount, $game->violet_odds, 2);
                        break;
                }
            }
        } else {
            if (!is_numeric($selected)) {
                return false;
            }
            $codes[$selected] = bcmul($contract_amount, $game->singular_odds, 2);
        }
        $estimated = [
            'EE0' => 0, 'EE1' => 0, 'EE2' => 0, 'EE3' => 0, 'EE4' => 0, 'EE5' => 0, 'EE6' => 0, 'EE7' => 0, 'EE8' => 0, 'EE9' => 0,
        ];
        for ($i = 0; $i < 10; $i++) {
            $estimated["EE{$i}"] = $codes[$i] ?? 0;
        }
        $issueSales = self::get(['issue_id'=>$issue->id]);
        if(!$issueSales){
            $insertData = [
                'issue_id'=>$issue->id,
                'issue'=>$issue->issue,
                'belongdate'=>date('Ymd'),
                'totalprice'=>$totalprice,
                'total_contract_amount'=>$contract_amount,
            ];
            self::create(array_merge($insertData, $estimated));
        }else{
            foreach ($estimated as $ek=>$ee) {
                if($ee >0){
                    $issueSales->setInc($ek,$ee);
                }
            }
            $issueSales->setInc('totalprice',$totalprice);
            $issueSales->setInc('total_contract_amount',$contract_amount);
        }
        return true;
    }
}