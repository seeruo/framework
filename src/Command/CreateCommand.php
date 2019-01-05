<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Library\Filesystem;
use Exception;


class CreateCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'create';
    protected $config;

    public function __construct()
    {
        $this->config = store('config');
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('创建一个文章模版文件')
            ->addArgument('filename', InputArgument::REQUIRED, '文件名称，不需要文件的后缀格式')
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
                'File Creator',
                '=====================',
            ]);

            $filename = $input->getArgument('filename');
            $this->create($filename);

            $output->writeln('Create <bg=yellow;>'.$filename.'.md</> <options=bold>Success!!!</>');
            $output->writeln('');
        } catch (Exception $e) {
            $output->writeln('<bg=red;>'.$e->getMessage().'</> <options=bold></>');
            $output->writeln('');
        }
    }
    /**
     * [create md file]
     * @DateTime 2018-12-13
     * @param    string     $filename 文件名称
     */
    protected function create(string $filename){
        $fileSystem = new Filesystem();
        $source = $this->config['source'] ?: 'Source';
        $file_path = ROOT . '/' . $source;
        // 创建文件路径
        $fileSystem->exists($file_path) || $fileSystem->mkdir($file_path);

        $html = '';
        $html .= "---" . PHP_EOL;
        $html .= "title: " . $filename . PHP_EOL;
        $html .= "type: " . 'type' . PHP_EOL;
        $html .= "date: " . date('Y-m-d H:i:s') . PHP_EOL;
        $html .= "tags: " . $filename . PHP_EOL;
        $html .= "---" . PHP_EOL . PHP_EOL;
        $html .= "Create at ".date('Y-m-d H:i:s').PHP_EOL;
        $html .= 'You should edit this page as markdown'.PHP_EOL;

        $file_name = $file_path . '/' . $filename . '.md';
        if ($fileSystem->exists([$file_name])) {
            throw new Exception('Create '.$filename.'.php failed, this file exited!!!');
        }
        
        $status = file_put_contents($file_name, $html, LOCK_EX);
        if ($status === false) {
            throw new Exception('Create '.$filename.' failed!');
        }
    }
}