<?php


namespace app\admin\command;


use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class Quasar extends Command
{
    protected function configure()
    {
        $this->setName('quasar')->setDescription('quasar stylus')
            ->addOption('compress', 'c', Option::VALUE_OPTIONAL, '是否压缩 .min', true);
    }

    protected function execute(Input $input, Output $output)
    {
        exec('stylus ./public/assets/stylus/quasar/index.styl -o ./public/assets/css/quasar/index.css');
        exec('stylus ./public/assets/stylus/quasar/index.styl -o ./public/assets/css/quasar/index.min.css --compress');
        exec('php think min -m all -r all');
        $output->info('Successful');
    }
}