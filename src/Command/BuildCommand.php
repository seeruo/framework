<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Library\Filesystem;
use App\Service\BuildService;
use Exception;


class BuildCommand extends Command
{
    protected static $defaultName = 'build';
    protected $config;

    public function __construct()
    {
        $this->config = store('config');
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('把markdown文件构建为静态页面')
            ->addOption(
                'active',
                'a',
                InputOption::VALUE_OPTIONAL,
                '是否主动编译?',
                false
            )
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
                'Start Building...',
                '<bg=yellow;>=================================</>',
            ]);


            $build = new BuildService();
            $active = $input->getOption('active');
            if ($active === false) {
                $build->run();
            }else{
                $build->listen();
            }

            $output->writeln('<bg=yellow;>Build Success!!!</>');
            $output->writeln('');
        } catch (Exception $e) {
            $output->writeln('<bg=red;>'.$e->getMessage().'</> <options=bold></>');
            $output->writeln('');
        }
    }
}