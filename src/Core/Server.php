<?php
namespace Seeruo\Core;

use Exception;
use \Seeruo\Core\File;
use \Seeruo\Core\Cmd;
use \Seeruo\Core\Log;


// $server=new MyServer();
// $server->listen();   //调用listen方法，使脚本处于监听状态
/**
 * 开启本地服务器,用于预览博客，不能用于生产环境
 * @socket 通信的整个过程
 * @socket_create   //创建套接字
 * @socket_bind     //绑定IP和端口
 * @socket_listen   //监听相应端口
 * @socket_accept   //接收请求
 * @socket_read     //获取请求内容
 * @socket_write    //返回数据
 * @socket_close    //关闭连接
 */
class Server
{
    private $ip;
    private $port;
    private $webroot;
    //将常用的MIME类型保存在一个数组中
    private $contentType=array(
        ".html"=>"text/html",
        ".htm"=>"text/html",
        ".xhtml"=>"text/html",
        ".xml"=>"text/html",
        ".php"=>"text/html",
        ".java"=>"text/html",
        ".jsp"=>"text/html",
        ".css"=>"text/css",
        ".ico"=>"image/x-icon",
        ".jpg"=>"application/x-jpg",
        ".jpeg"=>"image/jpeg",
        ".png"=>"application/x-png",
        ".gif"=>"image/gif",
        ".pdf"=>"application/pdf",
        );
    public function __construct($config){
        set_time_limit(0);
        $this->config = $config;
        $this->ip = $config['server_address'];
        $this->port = $config['server_port'];
        $this->webroot = $config['public_dir'];
        echo "\nServer init sucess\n";
    }
    public function listen(){
        try {
            $socket=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
            if(!$socket){
                throw new Exception("CREATE ERROR:".socket_strerror(socket_last_error()), 1);
            }
            $bool = @socket_bind($socket,$this->ip,$this->port);
            if(!$bool){
                throw new Exception("BIND ERROR:".socket_strerror(socket_last_error()), 1);
            }

            // 开启浏览器
            if ( $this->config['auto_open'] ) {
                if (strstr(PHP_OS, 'WIN')) {
                    $win_cmd = 'explorer http://'.$this->ip.':'.$this->port;
                    Cmd::system($win_cmd, $this->config['root'], 'Open Explorer');
                }else{
                    $mac_cmd = 'open http://'.$this->ip.':'.$this->port;
                    Cmd::system($mac_cmd, $this->config['root'], 'Open Explorer');
                }
            }
            Log::info('请浏览器里预览生成的网站效果，地址：http://'.$this->ip.':'.$this->port);

            // 轮训监听
            while(true){
                $bool = socket_listen($socket);
                if(!$bool){
                    throw new Exception("LISTEN ERROR:".socket_strerror(socket_last_error()), 1);
                }
                $new_socket = socket_accept($socket);
                if(!$new_socket){
                    throw new Exception("ACCPET ERROR:".socket_strerror(socket_last_error()), 1);
                }
                $string = socket_read($new_socket, 20480);
                $data = $this->request($string);
                $num = @socket_write($new_socket,$data);
                if($num == 0){
                    throw new Exception("WRITE ERROR:".socket_strerror(socket_last_error()), 1);
                }else{
                    // echo "request already succeed\n";
                }
                @socket_close($new_socket);
            }
        } catch (Exception $e) {
            Log::info($e->getMessage(), 'error');
        }
    }
    /**
     * [读取get或post请求中的url，返回相应的文件]
     * @param  [string]
     * @return [string]
     * http头
     * method url protocols
     */
    public function request($string){
        // echo $string;
        $pattern = "/\s+/";

        $request = preg_split($pattern,$string);
        if(count($request)<3){
            return "request error\n";
        }
        $filename = $this->webroot.$request[1];
        // 资源文件类型
        $type = $this->setContentType($filename);
        // 获取完整路径
        if ($filename === substr($filename,strpos($filename,'.'))) {
            if (strstr(PHP_OS, 'WIN')) {
                $filename = implode('/', array_filter(explode('/', $filename))).'/index.html';
            }else{
                $filename = '/'.implode('/', array_filter(explode('/', $filename))).'/index.html';
            }
        }
        if(file_exists($filename)){
            $data = file_get_contents($filename);
            return $this->addHeader($request[2],200,"OK",$data,$type);
        }else{
            $data = "this resource is not exists";
            return $this->addHeader($request[2],1000,"not exists",$data,$type);
        }
    }
    private function addHeader($protocol,$state,$desc,$str,$type){
         return "{$protocol} {$state} {$desc}\r\nContent-type:{$type}\r\n"."Content-Length:".
            strlen($str)."\r\nServer:MyServer\r\n\r\n".$str;
    }
    private function setContentType($filename){
        $type = substr($filename,strpos($filename,'.'));
        if(isset($this->contentType[$type])){
            return $this->contentType[$type];
        }else{
            return "text/html";
        }
    }
}