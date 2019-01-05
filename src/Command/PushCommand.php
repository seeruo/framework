<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\PushService;
use Exception;

class PushCommand extends Command
{
    protected static $defaultName = 'push';
    private $config;

    public function __construct()
    {
        $this->config = store('config');
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('推送静态网页到服务器/gitpages')
            ->addArgument('type', InputArgument::REQUIRED, '推送方式:ssh | git | init')
            ;
    }
    /**
     * [执行命令]
     * @DateTime 2018-12-13
     * @param    InputInterface  $input  输入对象
     * @param    OutputInterface $output 输出对象
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeln([
                '<bg=yellow;>Push Script Starting...</>',
                '=====================',
            ]);
            $push = new PushService($this->config);
            $type = $input->getArgument('type');
            $push->run($type);
            $output->writeln([
                '=====================',
                '<bg=yellow;>Push Script End!</>'
            ]);
        } catch (Exception $e) {
            $output->writeln('<bg=red;>'.$e->getMessage().'</> <options=bold></>');
            $output->writeln('');
        }
    }

}