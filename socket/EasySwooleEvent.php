<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Socket\Dispatcher;
use App\WebSocket\WebSocketParser;
use App\WebSocket\WebSocketEvent;
use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Db\Connection;
use App\Utility\Pool\PoolObject;
use EasySwoole\EasySwoole\ServerManager;
use App\Extend\Redis;
use App\Extend\Custom;

//use App\Extend\Redis as RedisSave;
class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register): void
    {
        // TODO: Implement mainServerCreate() method.

        /**
         * **************** websocket控制器 **********************
         */

        //创建一个 Dispatcher 配置
        $conf = new \EasySwoole\Socket\Config();
        //设置Dispatcher为WebSocket 模式
        $conf->setType(\EasySwoole\Socket\Config::WEB_SOCKET);
        //设置解析器对象
        $conf->setParser(new WebSocketParser());
        //TOKEN验证
        /*$register->set(EventRegister::onOpen, function ($ws, $request)  {
            //写入TOKEN
            $ws->bind($request->fd,100);
            var_dump($request);
        });*/

        //创建Dispatcher对象并注入config对象
        $dispatch = new Dispatcher($conf);
        //给server注册相关事件在WebSocket模式下onMessage事件必须注册 并且交给Dispatcher对象处理
        $register->set(EventRegister::onMessage, function (\swoole_websocket_server $server, \swoole_websocket_frame $frame) use ($dispatch) {
            $dispatch->dispatch($server, $frame->data, $frame);
        });
        //自定义握手事件
        $websocketEvent = new WebSocketEvent();
        //自定义关闭事件
        $register->set(EventRegister::onClose, function (\swoole_server $server, int $fd, int $reactorId) use ($websocketEvent) {
            $websocketEvent->onClose($server, $fd, $reactorId);
        });
        //DB类
        $DbConf = new \EasySwoole\ORM\Db\Config();
        $DbConf->setDatabase('live_dev');//库名称
        $DbConf->setUser('root');
        $DbConf->setPassword('%7%jYeCKg^NhP2wI');//数据库密码
        $DbConf->setHost('10.0.126.128');//地址
//        $DbConf->setPassword('!2x^mbVNQ^XBS^zevlUx');
//        $DbConf->setHost('127.0.0.1');
        //连接池配置
        $DbConf->setGetObjectTimeout(3.0); //设置获取连接池对象超时时间
        $DbConf->setIntervalCheckTime(30 * 1000); //设置检测连接存活执行回收和创建的周期
        $DbConf->setMaxIdleTime(15); //连接池对象最大闲置时间(秒)
        $DbConf->setMaxObjectNum(90); //设置最大连接池存在连接对象数量
        $DbConf->setMinObjectNum(30); //设置最小连接池存在连接对象数量
        $DbConf->setAutoPing(5); //设置自动ping客户端链接的间隔
        DbManager::getInstance()->addConnection(new Connection($DbConf));
        $register->add($register::onWorkerStart, function (\Swoole\Server $server,int $workerId) {
            //链接预热
            DbManager::getInstance()->getConnection()->getClientPool()->keepMin();
            // 自定义触发的事件
            Custom::getInstance()->start($server,$workerId);
        });

    }

    public static function onRequest(Request $request, Response $response): bool
    {

        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}