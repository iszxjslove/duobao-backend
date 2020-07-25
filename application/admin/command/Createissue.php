<?php


namespace app\admin\command;


use app\admin\model\Crontab;
use app\admin\model\Game;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use app\admin\model\Issue as IssueModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;


/**
 * cli 创建奖期
 * 功能 : CLI - 每个月定时生成各个彩种的奖期,默认生成下个月的奖期，可以手动指定相关的参数值
 * Class Issue
 * @package app\admin\command
 */
class Createissue extends Command
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
    private function destruct() {
        if ($this->doUnLock === TRUE) {
            $this->crontabModel->switchLock($this->getName(), $this->gid, FALSE, $this->getDescription()); //解锁计划任务
        }
    }

    protected function configure()
    {
        $this->setName('createissue')->setDescription('创建奖期')
            ->addOption('gid', 'g', Option::VALUE_REQUIRED, '游戏ID', null)
            ->addOption('first_issue', 'f', Option::VALUE_OPTIONAL, '第一期奖期号', 'auto')
            ->addOption('start_date', 's', Option::VALUE_OPTIONAL, '开始日期', null)
            ->addOption('end_date', 'e', Option::VALUE_OPTIONAL, '结束日期', null);
    }

    /**
     * @param Input $input
     * @param Output $output
     * @return bool|int|null
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    protected function execute(Input $input, Output $output)
    {
        $this->startRun = TRUE;

        // 找到游戏
        $this->gameModel = new Game();
        $this->gid = $input->getOption('gid');
        $game = $this->gameModel->where('id', $this->gid)->find();
        if(!$game){
            $output->error('游戏不存在');
            exit;
        }

        // 开始日期
        $start_date = $input->getOption('start_date');
        if (!$start_date) {
            $start_date = date('Y-m-01', strtotime("+1 month"));
        }
        // 结束日期
        $end_date = $input->getOption('end_date');
        if (!$end_date) {
            $end_date = date('Y-m-d', strtotime("$start_date +1 month -1 day"));
        }

        // Step 02: 在此 CLI 程序运行时, 获取独占锁. 禁止多进程同时运行
        $this->crontabModel = new Crontab();
        $flag = $this->crontabModel->switchLock($this->getName(), $this->gid, TRUE, $this->getDescription());
        if ($flag === FALSE) {
            $output->error('[d] [' . date('Y-m-d H:i:s') . '] The CLI ' . __CLASS__ . ' is running');
            exit;
        }
        $output->info("[Start Create] " . date('Y-m-d H:i:s'));
        $output->info("[Start From] " . $start_date);

        // Step 03: 生成奖期
        //检查指定日期的奖期是否存在，如果存在就停止运行
        $this->issueModel = new IssueModel();
        $first_issue = $input->getOption('first_issue');
        $startIssue = $this->issueModel->where(['game_id' => $this->gid, 'belongdate' => $start_date])->find(); // 开始日期的奖期
        $endIssue = $this->issueModel->find(['game_id' => $this->gid, 'belongdate' => $end_date]); // 结束日期的奖期
        if (empty($startIssue) && empty($endIssue)) { // 没有就生成
            $result = $this->issueModel->generalIssue($this->gid, $first_issue, strtotime($start_date), strtotime($end_date));
            if($result === false){
                $output->error("error: " . $this->issueModel->getError());
            }
            $output->info("Total Issue=" . $result);
            $output->info("[End To] " . $end_date);
        } else {
            $output->info("No need Create Issue!");
        }
        $this->doUnLock = TRUE;
        $output->info("[End Create] " . date('Y-m-d H:i:s'));

        return TRUE;
    }
}