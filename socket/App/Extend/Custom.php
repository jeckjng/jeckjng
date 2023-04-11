<?php
/**
 * Created by PhpStorm.
 * User: nms
 * Date: 2020/10/18
 * Time: 21:02
 */

namespace App\Extend;

use EasySwoole\Component\Singleton;
use EasySwoole\Http\AbstractInterface\Controller;
use App\WebSocket\ChatRoom;

class Custom extends Controller
{
    use Singleton;

    public function start($server,$workerId){

    }
}