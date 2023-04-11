<?php

namespace App\WebSocket;

use EasySwoole\Socket\AbstractInterface\ParserInterface;
use EasySwoole\Socket\Client\WebSocket;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;
use App\Extend\DesEcb;

/**
 * Class WebSocketParser
 *
 * 此类是自定义的 websocket 消息解析器
 * 此处使用的设计是使用 json string 作为消息格式
 * 当客户端消息到达服务端时，会调用 decode 方法进行消息解析
 * 会将 websocket 消息 转成具体的 Class -> Action 调用 并且将参数注入
 *
 * @package App\WebSocket
 */
class WebSocketParser implements ParserInterface
{
    /**
     * decode
     * @param  string $raw 客户端原始消息
     * @param  WebSocket $client WebSocket Client 对象
     * @return Caller         Socket  调用对象
     */
    public function decode($raw, $client): Caller
    {
        // 解析 客户端原始消息
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            echo "decode message error! \n";
            return null;
        }
        //$client->getFd();
        //处理FD
        $Desc = new DesEcb();
        $Desc->setFd($client->getFd(), $data);
        // new 调用者对象
        $caller = new Caller();
        /**
         * 设置被调用的类 这里会将ws消息中的 class 参数解析为具体想访问的控制器
         * 如果更喜欢 event 方式 可以自定义 event 和具体的类的 map 即可
         * 注 目前 easyswoole 3.0.4 版本及以下 不支持直接传递 class string 可以通过这种方式
         */
        $controller = isset($data['cont']) && $data['cont'] ? $data['cont'] : 'Index';
        $caller->setControllerClass('\\App\\WebSocket\\'.$controller);//设置控制器
        $type = ['login'=>'loginF', 'sync'=>'syncF','query'=>'queryF','orders_list'=>'ordersList','start_chat'=>'startChatRoom','send_msg'=>'chatRoomSendMsg','recommend_room'=>'recommendRoom'];//控制器数组
        $action = $type[$data['type']] ?? 'index';
        //echo $action."\n";
        $caller->setAction($action);
        $caller->setArgs($data);
        return $caller;
    }

    /**
     * 下面中的 : ? string 这里的 ? 表示返回值可以是null类型 而不是必须只能返回string类型
     * encode
     * @param  Response $response Socket    Response 对象
     * @param  WebSocket $client WebSocket Client   对象
     * @return string       发送给客户端的消息
     */
    public function encode(Response $response, $client): ?string
    {
        /**
         * 这里返回响应给客户端的信息
         * 这里应当只做统一的encode操作 具体的状态等应当由 Controller处理
         */
        return $response->getMessage();
    }
}
