<?php


namespace app\admin\command;


use app\admin\model\Crontab;
use app\admin\model\Game;
use app\admin\model\Issue as IssueModel;
use app\common\model\IssueSales;
use app\common\model\Projects;
use app\common\model\User;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class Sendbonus extends Command
{
    /**
     * @var int
     */
    protected $gid = 0;

    /**
     * @var Game
     */
    protected $gameModel = null;

    /**
     * @var Crontab
     */
    protected $crontabModel = null;

    /**
     * @var IssueModel
     */
    protected $issueModel = null;

    /**
     * @var Projects
     */
    protected $projectsModel = null;

    /**
     * @var bool 是否允许释放 LOCK 文件
     */
    private $doUnLock = FALSE;

    private $startRun = FALSE;

    private $iProcessRecord = 0;

    // 主要用于内部测试, 遇到无方案的奖期的情况,不中断程序.继续循环执行
    private $iRunTimes = 1;

    public function __destruct()
    {
        if ($this->startRun === TRUE) {
            $this->destruct();
        }
    }

    /**
     * 析构函数, 程序完整执行成功或执行错误后. 删除 locks 文件
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function destruct()
    {
        if ($this->doUnLock === TRUE) {
            $this->crontabModel->switchLock($this->getName(), $this->gid, FALSE, $this->getDescription()); //解锁计划任务
        }
    }

    protected function configure()
    {
        $this->setName('sendbonus')->setDescription('派奖')
            ->addOption('gid', 'g', Option::VALUE_REQUIRED, '游戏ID', null);
    }

    protected function execute(Input $input, Output $output)
    {
        $this->startRun = TRUE;
        // Step: 01 初步检测 CLI 参数合法性
        $this->gid = (int)$input->getOption('gid');

        // Step 02: 检查是否已有相同CLI在运行中
        $this->crontabModel = new Crontab();
        $flag = $this->crontabModel->switchLock($this->getName(), $this->gid, TRUE, $this->getDescription());
        if ($flag === FALSE) {
            $output->error('[d] [' . date('Y-m-d H:i:s') . '] The CLI ' . __CLASS__ . ' is running' . "\n");
            exit;
        }
        $output->info("[d] [" . date("Y-m-d H:i:s") . "] -------[ START SEND ]-------------\n");
        $result = $this->sendBonus($output);

        if ($result <= 0) {
            switch ($result) {
                case -1001 :
                    $sMsg = 'Wrong Lottery Id';
                    break;
                case -1002 :
                    $sMsg = 'ALL Done (All Issue)';
                    break;
                case -1003 :
                    $sMsg = 'Wrong IssueInfo (Issueid,Issue,code Err)';
                    break;
                case -1004 :
                    $sMsg = 'All Done (Single Issue)';
                    break;
                case -1008 :
                    $sMsg = 'Program holding! Waitting for table.IssueError Exception.';
                    break;
                case -2001 :
                    $sMsg = 'Transaction Start Failed.';
                    break;
                case -2002 :
                    $sMsg = 'Transaction RollBack Failed.';
                    break;
                case -2003 :
                    $sMsg = 'Transaction Commit Failed.';
                    break;
                case -3001 :
                    $sMsg = 'Issue Update Failed.';
                    break;
                case -3002 :
                    $sMsg = 'Project Update Failed.';
                    break;
                default :
                    $sMsg = 'Unknown ErrCode=' . $result;
                    break;
            }
            echo "[d] " . date('Y-m-d H:i:s') . " Message: {$sMsg}\n\n";
            $this->doUnLock = TRUE;
            return FALSE;
        }
        echo '[ ALL DONE ] Total Process Project Counts=' . (int)($result) . "\n\n";

        $this->doUnLock = TRUE;
        return TRUE;
    }

    private function sendBonus(Output $output): int
    {
        $this->gameModel = new Game();
        $game = $this->gameModel->where('id', $this->gid)->find();
        if (!$game) {
            return -1001; // 游戏ID错误
        }
        // 2, 获取需处理的奖期信息 ( From Table.`IssueInfo` )
        //     2.1  开奖号码已验证的         issueinfo.statuscode = 2
        //     2.2  完整执行中奖判断的       issueinfo.statuscheckbonus = 2
        //     2.3  未完整执行奖金派发的     issueinfo.statusbonus != 2
        //     2.4  符合当前彩种ID的         issueinfo.lotteryid = $iLotteryId
        //     2.5  已停售的                 issueinfo.saleend < 当前时间
        //     2.6  为了按时间顺序线性执行, 取最早一期符合以上要求的  ORDER BY A.`saleend` ASC
        $sCondition = [
            'statuscode'       => 2,
            'statuscheckbonus' => ['=', 2],
            'statusbonus'      => ['<>', 2],
            'saleend'          => ['<', time()]
        ];
        $fields = 'id,issue,code,last_digits,colors';
        $this->issueModel = new IssueModel();
        $issue = $this->issueModel->where($sCondition)->field($fields)->find();
        if (!$issue) {
            if (0 != $this->iProcessRecord) {
                $output->info('[d] Total Processed(projects): ' . $this->iProcessRecord . "\n");
            }
            return -1002; // 未获取到需要进行'中奖判断'的奖期号 (所有奖期的'中奖判断'皆以完成)
        }
        // 4, 获取所有已中奖, 并且尚未执行 '奖金派送' 的当期方案
        //     3.1  根据奖期号 $sIssue 查询方案表 projects
        //     3.2  '中奖状态' 状态为:    '中奖'  projects.`isgetprize`   = 1
        //     3.3  '奖金派送' 状态为: 非 '已派'  projects.`prizestatus` != 1
        $this->projectsModel = new Projects();
        $fields = 'id,user_id,issue,issue_id,color,code,contract_amount';
        $projectsList = $this->projectsModel->field($fields)->where(['issue' => $issue->issue, 'isgetprize' => 1, 'prizestatus' => 0])->select();
        $iCounts = count($projectsList);  // 实际获取的需处理方案个数
        $output->info("[d] [" . date('Y-m-d H:i:s') . "] Issue='$issue->issue', GotDataCounts='$iCounts' \n");

        // 5, 如果获取的结果集为空, 则表示当前奖期已全部'奖金派送'完成. 更新状态值
        if (!$iCounts) { // 奖期标记设置为: 已经完成奖金派发
            $issueSales = IssueSales::get(['issue_id' => $issue->id]);
            if ($issueSales) {
                $issueSales->actual_total_profit = bcsub($issueSales->totalprice, $issueSales->total_actual_expenditure, 2);
                $issueSales->save();
            }
            $result = $this->projectsModel->where(['issue' => $issue->issue, 'prizestatus' => 0])->update(['isgetprize' => 2]);
            if ($result === false) { // update 出错
                return -3002;
            }
            $result = $this->projectsModel->where(['issue' => $issue->issue])->update(['no_code' => $issue->code, 'no_colors' => $issue->colors]);
            if ($result === false) { // update 出错
                return -3002;
            }
            $result = $this->issueModel->where(['statusbonus' => ['<', 2], 'issue' => $issue->issue, 'statuscheckbonus' => 2])->update(['statusbonus' => 2]);
            if (!$result) {
                return -3001;// 更新奖期状态值失败 (可能判断中奖未完成执行)
            }
            // 生成单期盈亏数据
            // ................
            //添加奖期风控数据
            // ................
            return 1;
        }

        // 奖期标记设置为: 进行'奖金派送'中
        $this->issueModel->where(['issue' => $issue->issue])->update(['statusbonus' => 1]);

        // 中奖的颜色
        $bonusColors = explode(',', $issue->colors);
        // 对应的奖励倍数
        $powers = [
            'green'    => [$game->green_lucky_odds, $game->green_ordinary_odds],
            'red'      => [$game->red_lucky_odds, $game->red_ordinary_odds],
            'violet'   => [$game->violet_odds],
            'singular' => [$game->singular_odds]
        ];
        $countBonusColors = count($bonusColors);
        if ($countBonusColors !== 1 && $countBonusColors !== 2) {
            return -1009;
        }

        foreach ($projectsList as $item) {
            switch ($item->color) {
                case 'singular':
                    $item->bonus = bcmul($item->contract_amount, $powers['singular'][0], 2);
                    break;
                case 'violet':
                    $item->bonus = bcmul($item->contract_amount, $powers['violet'][0], 2);
                    break;
                case 'green':
                    $pow = $powers['green'][1];
                    if ($issue->last_digits === 5) {
                        $pow = $powers['green'][0];
                    }
                    $item->bonus = bcmul($item->contract_amount, $pow, 2);
                    break;
                case 'red':
                    $pow = $powers['red'][1];
                    if ($issue->last_digits === 0) {
                        $pow = $powers['red'][0];
                    }
                    $item->bonus = bcmul($item->contract_amount, $pow, 2);
                    break;
            }

            if ($item->bonus) {
                User::money($item->user_id, $item->bonus, "{$item->issue}: 派发奖金");
                IssueSales::where(['issue_id' => $item->issue_id])->setInc('total_actual_expenditure', $item->bonus);
                $item->prizestatus = 1;
                $item->bonustime = time();
                $this->iProcessRecord++;
            }
            $item->save();
        }

        if ($this->iRunTimes > 0) { // 多次执行（实际会在下一次执行后退出递归），以便在派奖完成后，再次处理未中奖的订单
            $output->info("[d] [" . date('Y-m-d H:i:s') . "] Run time Countdown: {$this->iRunTimes}. \n");
            $this->iRunTimes--;
            $this->sendBonus($output); // 递归执行派奖
        }
        // 6, 返回负数表示错误, 正数表示本次 CLI 执行受影响的方案数
        return $this->iProcessRecord;
    }
}