<?php


namespace app\admin\command;


use app\admin\model\Crontab;
use app\admin\model\finance\Order;
use app\admin\model\Game;
use app\admin\model\Issue as IssueModel;
use app\common\model\Projects;
use app\common\model\User;
use app\common\model\UserFinance;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class SendInterest extends Command
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
        $this->setName('sendInterest')->setDescription('金额收益入账');
    }

    protected function execute(Input $input, Output $output)
    {
        $this->startRun = TRUE;

        // Step 02: 检查是否已有相同CLI在运行中
        $this->crontabModel = new Crontab();
        $flag = $this->crontabModel->switchLock($this->getName(), $this->gid, TRUE, $this->getDescription());
        if ($flag === FALSE) {
            $output->error('[d] [' . date('Y-m-d H:i:s') . '] The CLI ' . __CLASS__ . ' is running' . "\n");
            exit;
        }
        $output->info("[d] [" . date("Y-m-d H:i:s") . "] -------[ START SEND ]-------------\n");

        $this->sendInterest($output);

        $this->doUnLock = TRUE;
        return TRUE;
    }

    private function sendInterest(Output $output)
    {
        $financeOrder = new  Order();
        /**
         * status == 1  计息中
         * next_period_time <= time()  首次计息时间小于当前时间
         */
        $condition = [
            'status'           => 1,
            'next_period_time' => ['<=', time()]
        ];
        $list = $financeOrder->where($condition)->order('next_period_time')->limit(10000)->select();
        foreach ($list as $item) {
            // 利息
            $interest = bcmul($item->remaining_amount, $item->rate);
            if ($item->interest_where === 'finance') {
                // 转入余额宝
                UserFinance::moneyByUserId($item->user_id, $interest, $item->title);
            } else {
                // 转入余额
                User::money($item->user_id, $interest, $item->title);
            }
            // 下一个计息日期
            $next_period_time = strtotime("+{$item->period} {$item->period_unit}");
            if($item->end_time && $next_period_time > $item->end_time){
                $item->status = 2;
            }else{
                $item->next_period_time;
            }
            $item->save();
        }
    }
}