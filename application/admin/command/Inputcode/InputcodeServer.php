<?php

namespace app\admin\command\Inputcode;

use app\admin\model\Issue;
use think\console\Output;

class InputcodeServer
{
    /**
     * @var Output
     */
    private $output;

    /**
     * InputcodeServer constructor.
     * @param Output $output
     */
    public function __construct(Output $output)
    {
        $this->output = $output;
    }


    /**
     * 更新开奖号码
     */
    public function doInputCode($gid = 0)
    {
        //找出最近没有开奖的奖期 ( statuscode < 2 )
        $issueModel = new Issue();
        if (!$aLastNoDrawIssue = $issueModel->getLastNoDrawIssue($gid)) {
            $this->output->warning("[n] [" . date('Y-m-d H:i:s') . "] getLastNoDrawIssue was Empty!\n");
            return FALSE;
        }

        $iWaitTime = $aLastNoDrawIssue['saleend'] - time();
        if ($iWaitTime > 0) {
            // 如果: '当前时间' 早于 '最近期销售截止时间', 则: 仍在销售期, 程序正常中断, 等待下次运行
            $this->output->info("[n] [" . date('Y-m-d H:i:s') . "] Curent Issue:[{$aLastNoDrawIssue['issue']}] Is SaleIng, await.\n");
            return FALSE;
        }

        $code = substr(date('Ymd') / 17658, -3) . random_int(100, 999);
        $last_digits = substr($code, -1);
        $colors_list = [
            'green'  => ['1', '3', '5', '7', '9'],
            'red'    => ['0', '2', '4', '6', '8'],
            'violet' => ['0', '5']
        ];
        $colors = [];
        foreach ($colors_list as $key => $color_nums) {
            if (in_array($last_digits, $color_nums, true)) {
                $colors[] = $key;
            }
        }
//        $sExpectedIssue = $aLastNoDrawIssue['issue']; //最近应该开奖的奖期
//
//        $sCode = $oIssueInfo->getSetIssueCode($iLotteryId, $aLastNoDrawIssue);
//
//        if(!$sCode) {
//            //$sCode = $this->_makeBestCode($iLotteryId, $aLastNoDrawIssue);
//            $sCode = $this->doMakeCode($iLotteryId, $aLastNoDrawIssue);
//        }
//        if(!$sCode) {
//            echo "[n] [" . date('Y-m-d H:i:s') . "] Curent Issue:[{$aLastNoDrawIssue['issue']}] doMakeCode FAILED.\n";
//            return FALSE;
//        }
//
        $iWaitTime = $aLastNoDrawIssue['earliestwritetime'] - time();
        if ($iWaitTime > 0) { // 还未到允许录号时间
            $this->output->info("[n] [" . date('Y-m-d H:i:s') . "] Curent Issue:[{$aLastNoDrawIssue['issue']}] Waiting to drawNumber({$iWaitTime}s)\n");
            sleep($iWaitTime); // 等待允许录号时间
        }

        // 执行开奖动作
        $this->output->info("[n] [" . date('Y-m-d H:i:s') . "] Processing Curent Issue:[{$aLastNoDrawIssue['issue']}].\n");
//        $mResult = $oIssueInfo->drawNumber($iLotteryId, $sExpectedIssue, $sCode, 100, 255, 'sysEcho');
        $mResult = TRUE;
        if ($mResult === TRUE) {
            \app\api\model\Issue::update([
                'id'          => $aLastNoDrawIssue['id'],
                "code"        => $aLastNoDrawIssue['code'] ?: $code,
                "last_digits" => $last_digits,
                "colors"      => implode(',', $colors),
                "statuscode"  => 2
            ]);
//            $this->oDB->update("issueinfo", array("statusfetch" => 2, "statuslocks" => 2), "`lotteryid`=" . $iLotteryId . " AND `issue`='" . $sExpectedIssue . "'");
        }
        return $mResult;
    }

}