<?php


namespace app\admin\command;


use app\admin\model\Crontab;
use app\admin\model\Game;
use app\admin\model\Issue as IssueModel;
use app\common\model\Projects;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;

class Checkbonus extends Command
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
        $this->setName('checkbonus')->setDescription('中奖判断')
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

        $output->info("[d] [" . date("Y-m-d H:i:s") . "] -------[ START CHECK ]-------------\n");

        $result = $this->checkbonus($output);
        if ($result <= 0) {
            switch ($result) {
                case -1001 :
                    $sMsg = 'Wrong Lottery Id';
                    break;
                case -1002 :
                    $sMsg = 'ALL DONE (All Issue)';
                    break;
                case -1003 :
                    $sMsg = 'Wrong IssueInfo';
                    break;
                case -1004 :
                    $sMsg = 'ALL DONE (Single Issue)';
                    break;
                case -1005 :
                    $sMsg = 'Data Init Failed!';
                    break;
                case -1006 :
                    $sMsg = 'doProcess() Method Not Exists!';
                    break;
                case -1007 :
                    $sMsg = 'doProcess() Update Failed!';
                    break;
                case -1009 :
                    $sMsg = "Projects Bonus Colors Exception";
                    break;
                case  -1010:
                    $sMsg = 'Projects isgetprize Update Failed';
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
                    $sMsg = 'issueinfo.statuscheckbonus Update Failed.';
                    break;
                default :
                    $sMsg = 'Unknown ErrCode=' . $result;
                    break;
            }
            echo "[d] " . date('Y-m-d H:i:s') . " Message: {$sMsg}\n\n";
            $this->doUnLock = TRUE;
            return FALSE;
        }

        echo '[ ALL DONE ] Total Process Counts=' . (int)($result) . "\n\n";
        $this->doUnLock = TRUE;
        return TRUE;
    }

    private function checkbonus(Output $output)
    {

        $this->gameModel = new Game();
        $game = $this->gameModel->where('id', $this->gid)->find();
        if (!$game) {
            return -1001; // 游戏ID错误
        }

        // Step 03, 获取需处理的奖期信息 ( From Table.`IssueInfo` )
        //     2.1  录入开奖号码并已验证的   statuscode = 2
        //     2.2  未完整执行中奖判断的     statuscheckbonus != 2
        //     2.3  符合当前彩种ID的         lotteryid = $iLotteryId
        //     2.4  已停售的                 saleend < 当前时间
        //     2.5  录入的开奖号码不为空     code != ''
        //     2.6  为了按时间顺序线性执行,取最早一起符合以上要求的  ORDER BY A.`saleend` ASC

        $this->issueModel = new IssueModel();
        $sCondition = [
            'statuscode'       => 2,
            'statuscheckbonus' => ['<>', 2],
            'saleend'          => ['<', time()],
            'code'             => ['NEQ', '']
        ];
        $fields = 'id,issue,code,last_digits,colors';
        $issue = $this->issueModel->where($sCondition)->field($fields)->find();
        if (!$issue) {
            return -1002; // 未获取到需要进行'中奖判断'的奖期号 (所有奖期的'中奖判断'皆以完成)
        }
        // Step 04, 如果获取的结果集为空, 则表示当前奖期已完成全部'中奖判断'.更新状态值
        $this->projectsModel = new Projects();

        // 获取需处理 '中奖判断' 下注统计
        $notGetCount = $this->projectsModel->where(['issue' => $issue->issue, 'isgetprize' => 0])->count();
        $output->info("[d] [" . date('Y-m-d H:i:s') . "] Issue='$issue->issue', game_id='$game->id', Distinct(Projects)='$notGetCount' ");

        if ($notGetCount === 0) {
            $update['statuscheckbonus'] = 2;
            $result = $this->issueModel->where(['id' => $issue->id])->update($update);
            if ($result === false) {
                return -3001; // 更新奖期状态值失败
            }
            return 1;
        }
        // 奖期标记设置为: 进行'中奖判断'中.
        $update['statuscheckbonus'] = 1;
        $this->issueModel->where(['id' => $issue->id])->update($update);
        unset($update);

        /**
         * 6, 对当前彩种的所有未进行中奖判断
         * 消息类型
         *   [n]   表示 notice 错误, 并不重要
         *   [w]   表示 warnning 错误, 重要!!!
         *   [d]   表示 debug 消息
         */

        // 中奖的颜色
        $bonusColors = explode(',', $issue->colors);
        $countBonusColors = count($bonusColors);
        if ($countBonusColors !== 1 && $countBonusColors !== 2) {
            return -1009;
        }
        $condition = [
            'issue'      => $issue->issue,
            'isgetprize' => 0
        ];
        $isWhere = "`code` = '{$issue->last_digits}'";
        if (count($bonusColors) === 1) {
            // 红色或绿色
            $isWhere .= " OR `color` = '{$issue->colors}'";
        } elseif (count($bonusColors) === 2) {
            // 红和紫或者绿和紫
            $isWhere .= " OR `color` = '{$bonusColors[0]}' OR `color` = '{$bonusColors[1]}'";
        }
        $totalProcessed = 0;
        $result = Projects::where($condition)->whereRaw($isWhere)->update(['isgetprize' => 1]);
        if ($result === false) {
            return -1010;
        }
//        $totalProcessed += $result;
//        $result = Projects::where(array_merge($condition, $notWhere))->update(['isgetprize' => 2]);
//        if ($result === false) {
//            return -1010;
//        }
        $totalProcessed += $result;
        $result = $this->issueModel->where(['id' => $issue->id])->update(['statuscheckbonus' => 2]);
        if ($result === false) {
            return -3001; // 更新奖期状态值失败
        }
        return $totalProcessed;
    }
}