<?php


namespace app\admin\command;


use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class CheckSendBonus extends Command
{
    protected function configure()
    {
        $this->setName('checksendbonus')->setDescription('中奖判断、派奖')
            ->addOption('gid', 'g', Option::VALUE_REQUIRED, '游戏ID', null);
    }

    protected function execute(Input $input, Output $output)
    {
        exec('php think checkbonus -g 1');
        exec('php think sendbonus -g 1');
    }
}