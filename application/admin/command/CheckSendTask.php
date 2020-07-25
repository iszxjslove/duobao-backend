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
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class CheckSendTask extends Command
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
        $this->setName('checksendtask')->setDescription('中奖判断、派奖')
            ->addOption('gid', 'g', Option::VALUE_REQUIRED, '游戏ID', null)
            ->addOption('count', 't', Option::VALUE_OPTIONAL, '重复次数', 10);
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

        $this->doUnLock = TRUE;
        exec("php think checkbonus -g {$this->gid}");
        $this->doUnLock = TRUE;
        return TRUE;
    }
}