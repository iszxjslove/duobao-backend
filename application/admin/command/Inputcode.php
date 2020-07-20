<?php


namespace app\admin\command;


use app\admin\command\inputcode\InputcodeServer;
use app\admin\model\Crontab;
use app\admin\model\Game;
use app\admin\model\Issue as IssueModel;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class Inputcode extends Command
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

    protected function configure()
    {
        $this->setName('inputcode')->setDescription('输入开奖号码')
            ->addOption('gid', 'g', Option::VALUE_REQUIRED, '游戏ID', null)
            ->addOption('count', 't', Option::VALUE_OPTIONAL, '重复次数', 10);
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
        // Step: 01 初步检测 CLI 参数合法性
        $this->gameModel = new Game();
        $this->gid = $input->getOption('gid');
        $game = $this->gameModel->where('id', $this->gid)->find();
        if (!$game) {
            $output->error('游戏不存在');
            exit;
        }

        // Step 02: 检查是否已有相同CLI在运行中
        $this->crontabModel = new Crontab();
        $flag = $this->crontabModel->switchLock('inputcode', $this->gid, TRUE);
        if ($flag === FALSE) {
            $output->error('[d] [' . date('Y-m-d H:i:s') . '] The CLI ' . __CLASS__ . ' is running');
            exit;
        }
        $output->info("[d] [" . date('Y-m-d H:i:s') . "] -------[ START TESTINPUTCODE ]-------");

        $oInputCode = new InputcodeServer($output);
        $count = $input->getOption('count');
        do {
            $flag = $oInputCode->doInputCode($this->gid);
//            var_dump($flag);
//            exit;
//            if ($flag !== FALSE) {
//                $output->info("[d] [" . date('Y-m-d H:i:s') . "] inputcode success\n\n");
//            } else {
//                $output->info("[d] [" . date('Y-m-d H:i:s') . "] inputcode Fail\n\n");
//            }
            $count--;
        } while ($flag === TRUE && $count > 0);
        $output->info("[d] [" . date('Y-m-d H:i:s') . "] -------[ START CheckSendTask ]-------\n");
//        require( realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'e1m_check_send_task.php'); // 中奖判断、派奖类文件、追号单转注单


        $this->doUnLock = TRUE;
        $output->info("[End Create] " . date('Y-m-d H:i:s'));

        $this->destruct();
        return TRUE;
    }

    /**
     * 析构函数, 程序完整执行成功或执行错误后. 删除 locks 文件
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    private function destruct()
    {
        if ($this->doUnLock === TRUE) {
            $this->crontabModel->switchLock($this->getName(), $this->gid, FALSE); //解锁计划任务
        }
    }
}