<?php
/**
 * Created by PhpStorm.
 * User: nms
 * Date: 2021/3/13
 * Time: 20:34
 */

namespace APP\Extend;

use EasySwoole\Component\Singleton;
use App\Extend\Redis as redisModel;
use App\Model\KaModel;
use APP\Model\UsersModel;

class DesEcb
{

    use Singleton;

    public function getUrl()
    {
        return "http://104.194.232.198/Pay/setNotifyUrl";
    }

    /***
     * 修改FD
     **/
    public function setFd($fd, $mobile)
    {
        if (is_array($mobile)) {
            return;
        }
        //查询redis
        if (redisModel::getInstance()->clientRedis()->hGet('DF' . $mobile, 'fd') == $fd) {
            return 200;
        }
        $model = new KaModel();//查询
        $modelData = $model->getStatus($mobile);
        if (isset($modelData[1]['status']) && $modelData[1]['status'] == 1) {
            redisModel::getInstance()->clientRedis()->hSet('DF' . $mobile, 'fd', $fd);
            redisModel::getInstance()->clientRedis()->hSet('DF' . $mobile, 'lock', 0);
            redisModel::getInstance()->clientRedis()->hSet('DF' . $mobile, 'ord_id', 0);//记录本次操作的ID
            redisModel::getInstance()->clientRedis()->hSet('DF' . $mobile, 'ka', $modelData[1]['ka']);//记录卡号
            //redisModel::getInstance()->clientRedis()->hSet('DF' . $mobile, 'ka_id', $modelData[1]['id']);//记录卡号
            return 200;
        }
    }

    /**
     * 设置订单号别名
     **/
    public function setAsOrId($mobile, $id)
    {
        return redisModel::getInstance()->clientRedis()->hSet('DF' . $mobile, 'ord_as_id', $id);
    }

    /***
     * 获取订单别名
     **/
    public function getAsOrId($mobile)
    {
        return redisModel::getInstance()->clientRedis()->hGet('DF' . $mobile, 'ord_as_id');
    }

    /**
     * 获取卡ID
     **/
    public function getKaId($mobile)
    {
        return redisModel::getInstance()->clientRedis()->hGet('DF' . $mobile, 'ka_id');
    }

    /***
     * 获取FD
     **/
    public function getFD($mobile)
    {
        return redisModel::getInstance()->clientRedis()->hGet('DF' . $mobile, 'fd');
    }

    /**
     * 设置本次执行订单号
     **/
    public function setOrderId($mobile, $id)
    {
        return redisModel::getInstance()->clientRedis()->hSet('DF' . $mobile, 'ord_id', $id);
    }

    /**
     * 读取本次执行订单号
     **/
    public function getOrderId($mobile)
    {
        return redisModel::getInstance()->clientRedis()->hGet('DF' . $mobile, 'ord_id');
    }

    /**
     * 清理本次执行的订单
     **/
    public function delOrderId($mobile)
    {
        redisModel::getInstance()->clientRedis()->hSet('DF' . $mobile, 'data', '');
        redisModel::getInstance()->clientRedis()->hSet('DF' . $mobile, 'ord_id', 0);
        return;
    }

    /**
     * 读取卡号
     **/
    public function getKaNum($mobile)
    {
        return redisModel::getInstance()->clientRedis()->hGet('DF' . $mobile, 'ka');
    }

    /**
     * 查看FD是否解锁
     **/
    public function getLock($mobile)
    {
        return redisModel::getInstance()->clientRedis()->hGet('DF' . $mobile, 'lock');
    }

    /**
     * 设置FD枷锁
     **/
    public function setLock($mobile)
    {
        redisModel::getInstance()->clientRedis()->hSet('DF' . $mobile, 'lock', 1);
    }

    /**
     * FD解锁
     **/
    public function unLock($mobile)
    {
        return redisModel::getInstance()->clientRedis()->hSet('DF' . $mobile, 'lock', 0);
    }

    /**
     * 设置金额
     **/
    public function setBalance($mobile, $balance)
    {
        return redisModel::getInstance()->clientRedis()->hSet('DF' . $mobile, 'balance', $balance);
    }

    /***
     * 读取金额
     **/
    public function getBalance($mobile)
    {
        return redisModel::getInstance()->clientRedis()->hGet('DF' . $mobile, 'balance');
    }

    /***
     * 判断金额字段存在
     **/
    public function balanceExists($mobile)
    {
        return redisModel::getInstance()->clientRedis()->hExists('DF' . $mobile, 'balance');
    }

    /***
     * 获取uid对应的数据
     **/
    public function getOrder($uid)
    {
        $model = new UsersModel();//查询
        $order = $model->getOrderData($uid);
        if ($order[0] != 200) {
            return [];
        }
        $this->response()->setMessage($order);
        return $order[1];
        redisModel::getInstance()->clientRedis()->hSet('DF' . $mobile, 'data', json_encode($order[1]));
        return $order[1];
    }

    /***
     * 获取订单信息
     **/
    public function getOrderData($mobile)
    {
        return json_decode(redisModel::getInstance()->clientRedis()->hGet('DF' . $mobile, 'data'), true);//返回数组
    }

    /***
     * 设置金额
     ****/
    public function setBalanceData($id, $balance)
    {
        if ($balance > 0 && is_numeric($balance)) {
            $model = new KaModel();//查询
            return $model->setBalance((int)$id, (float)$balance);

        }
        return false;
    }

    /**
     *
     * 设置订单为支付状态
     **/
   public function setOrderStatus($id)
    {
        $model = new KaModel();//查询
        $status = $model->setOrderStatus($id, 2);
        if ($status[0] != 200) {
            return 400;
        }
        return 200;
    }
    public function getAtotask($id)
    {
        return json_decode(redisModel::getInstance()->clientRedis()->hGet('atmosphere_' . $id, $id), true);//返回数组
    }
    public function getuseUser()
    {
        return redisModel::getInstance()->clientRedis()->zRange('user_use_fictitions',0,0,true);//返回数组
    }
    public function deluseUser()
    {
        return redisModel::getInstance()->clientRedis()->zDelete('user_use_fictitions','202106172341040');//返回数组
    }
    /***
     * 设置fd，写入redis
     **/
    public function setFid()
    {
        return redisModel::getInstance()->clientRedis()->hSet('fid_data', '1',2);
    }
    public function setFidbystream($enterdata)
    {
        return redisModel::getInstance()->clientRedis()->hSet('fid_'.$enterdata['stream'], $enterdata['uid'],$enterdata['token']);
    }
    public function getFidbystream($enterdata)
    {
        return redisModel::getInstance()->clientRedis()->hGetAll('fid_'.$enterdata['stream']);
    }
    public function zremuseUser($deluid)
    {
        return redisModel::getInstance()->clientRedis()->zRem('user_use_fictitions',$deluid,true);//返回数组
    }
    //删除虚拟用户登录的token
    public function delFidbystream($enterdata)
    {
        return redisModel::getInstance()->clientRedis()->hDel('fid_'.$enterdata['stream'],$enterdata['uid']);
    }
    //删除虚拟用户登录的token（退出的时候，整个删除）
    public function delFidstream($enterdata)
    {
        return redisModel::getInstance()->clientRedis()->hDel('fid_'.$enterdata['stream']);
    }
    //删除在房间的虚拟用户信息
    public function delUserstrem($enterdata)
    {
        return redisModel::getInstance()->clientRedis()->zRem('user_'.$enterdata['stream'],$enterdata['uid'],true);//返回数组
    }
     //获取会员的名称
    public function getUserinfo($uid)
    {
        return json_decode(redisModel::getInstance()->clientRedis()->get('userinfo_'.$uid),true);
    }
    //删除在房间的虚拟用户信息
    public function getUserstremsize($stream)
    {
        return redisModel::getInstance()->clientRedis()->zCard('user_'.$stream);//返回数组
    }
    //记录跟投和消息队列
    public function addQueuedata($data)
    {
        return redisModel::getInstance()->clientRedis()->lPush('test_queue',$data);//返回数组
    }
    //备份跟投和消息队列
    public function copyQueuedata($data)
    {
        return redisModel::getInstance()->clientRedis()->lPush('test_queue001',$data);//返回数组
    }
    //获取虚拟会员的token值
    public function getUsertoken($uid)
    {
        return json_decode(redisModel::getInstance()->clientRedis()->get('token'.$uid),true);
    }
    //测试
    public function testlpush($data)
    {
        return redisModel::getInstance()->clientRedis()->lPush('vutar_stream_'.$data['stream'],json_encode($data));//返回数组
    }
    public function testlpop($stream)
    {
        return redisModel::getInstance()->clientRedis()->lPop('vutar_stream_'.$stream);//返回数组
    }




}