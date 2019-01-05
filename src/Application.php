<?php
namespace App;

use Symfony\Component\Console\Application AS Console;
use App\Command\BuildCommand;
use App\Command\CreateCommand;
use App\Command\ServerCommand;
use App\Command\PushCommand;

class Application 
{
    private $console;

    public function __construct(array $config=[])
    {
        // 配置文件处理
        $config['port']   = $config['port'] ?: 9001;
        $config['public'] = $config['public'] ?: 'Public';

        store()->set('config', $config);

        $console = new Console();
        $console->add(new BuildCommand());   // 构建指令
        $console->add(new CreateCommand());  // 创建模版的指令
        $console->add(new ServerCommand());  // 本地服务器指令
        $console->add(new PushCommand());  // 本地服务器指令
        $this->console = $console;
    }

    public function run()
    {
        $this->console->run();
    }
}
