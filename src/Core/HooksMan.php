<?php
namespace Seeruo\Core;

/** 
 * 钩子管理
 */
class HooksMan
{  
    //声明一个私有的实例变量
    static private $hooks;
    //声明私有构造方法为了防止外部代码使用new来创建对象。
    private function __construct($conf){
        // 初始钩子
        $pluginList = scandir($conf['plugin_dir']);
        // 循环插件 // 排除. ..
        foreach ($pluginList as $k => $v) {
            if ($v=='.' || $v=='..') {
                unset($pluginList[$k]);
            }
        }
        // 插件管理
        foreach ($pluginList as $k => $v) {
            // 获取配置项
            $config = include_once($conf['plugin_dir']. DIRECTORY_SEPARATOR .$v.'/config.php');
            if ($config['status'] == 1) {
                include_once($conf['plugin_dir']. DIRECTORY_SEPARATOR .$v.'/index.php');
                if (class_exists($v))
                {
                    //初始化所有插件  
                    new $v($this);
                }
            }
        }
    }

    static public $instance;//声明一个静态变量（保存在类中唯一的一个实例）
    static public function getinstance($config){
        if(!self::$instance){
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * [add 注册插件]
     * @Author   danier     cdking95@gmail.com
     * @DateTime 2018-08-25
     * @param    [type]     &$class            [插件对象]
     * @param    [type]     $hook              [钩子名]
     * @param    [type]     $method            [方法名]
     */
    static public function add(&$class, $hook, $method)
    {
        if (!isset(self::$hooks[$hook][$method])) {
            self::$hooks[$hook][$method] = [
                'class'  => &$class,
                'method' => $method
            ];
        }
    }

    /**
     * [run 执行插件]
     * @Author   danier     cdking95@gmail.com
     * @DateTime 2018-08-25
     * @param    [type]     $hook              [插件名]
     * @param    [type]     $param             [参数]
     * @return   [type]
     */
    static public function run($hook, $params='')
    {
        $result = '';
        //查看要实现的钩子，是否在监听数组之中  
        if (isset(self::$hooks[$hook]) && is_array(self::$hooks[$hook]) && count(self::$hooks[$hook]) > 0) {  
            // 循环调用开始  
            foreach (self::$hooks[$hook] as $listener) {  
                $class  = &$listener['class'];
                $method = $listener['method'];
                if(method_exists($class, $method)) {  
                    // 动态调用插件的方法  
                    $result .= $class->$method($params);  
                }  
            }  
        }  
        return $result;  
    }
}  