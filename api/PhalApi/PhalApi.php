<?php
/**
 * 框架版本号
 */
defined('PHALAPI_VERSION') || define('PHALAPI_VERSION', '1.3.5');
 
/**
 * 项目根目录
 */
defined('PHALAPI_ROOT') || define('PHALAPI_ROOT', dirname(__FILE__));

require_once PHALAPI_ROOT . DIRECTORY_SEPARATOR . 'PhalApi' . DIRECTORY_SEPARATOR . 'Loader.php';

/**
 * PhalApi 应用类
 *
 * - 实现远程服务的响应、调用等操作
 * 
 * <br>使用示例：<br>
```
 * $api = new PhalApi();
 * $rs = $api->response();
 * $rs->output();
```
 *
 * @package     PhalApi\Response
 * @license     http://www.phalapi.net/license GPL 协议
 * @link        http://www.phalapi.net/
 * @author      dogstar <chanzonghuang@gmail.com> 2014-12-17
 */

class PhalApi {
    
    /**
     * 响应操作
     *
     * 通过工厂方法创建合适的控制器，然后调用指定的方法，最后返回格式化的数据。
     *
     * @return mixed 根据配置的或者手动设置的返回格式，将结果返回
     *  其结果包含以下元素：
```
     *  array(
     *      'ret'   => 200,	            //服务器响应状态
     *      'data'  => array(),	        //正常并成功响应后，返回给客户端的数据	
     *      'msg'   => '',		        //错误提示信息
     *  );
```
     */
    public function response() {
        $rs = DI()->response;
        $service = DI()->request->get('service', 'Default.Index');

        try {
            $redis = connectionRedis();
            $redis->set('http_root_url',get_protocal().'://'.$_SERVER['HTTP_HOST'],60*60*24*30);
            if($redis->get('logapi_reqeuest_status') == 1){
                logapi(array_merge($_GET,$_POST),'【PhalApi接口请求日志】');  // 接口日志记录
            }

            // 接口响应
            $api = PhalApi_ApiFactory::generateService();
            list($apiClassName, $action) = explode('.', $service);
            $data = call_user_func(array($api, $action));

            $rs->setData($data);
        } catch (PhalApi_Exception $ex) {
            // 框架或项目的异常
            $rs->setRet($ex->getCode());
            $rs->setMsg($ex->getMessage());
            logapi(array_merge($_GET,$_POST),'【PhalApi接口请求失败】PhalApi_Exception: '.$ex->getMessage());  // 接口日志记录
        } catch (Exception $ex) {
            // 不可控的异常
            DI()->logger->error($service, strval($ex));
            logapi(array_merge($_GET,$_POST),'【PhalApi接口请求失败】'.$ex->getMessage());  // 接口日志记录
            $data = array('code' => 2081, 'msg' => codemsg('2081'), 'info' => array());
            $rs->setData($data);
        }

        return $rs;
    }

    public function ifGoAppApiRqquest($service){
        $service = strtolower($service);
        $goApiList = array(
            // 贵族
            strtolower('Live.GetNobleList'),
            strtolower('Live.GetNobleUserInfo'),
            strtolower('Live.BuyNoble'),
            strtolower('Live.GetNobleSetting'),
            // 坐骑
            strtolower('Live.GetCarList'),
            strtolower('Live.GetCarListAll'),
            strtolower('Live.GetUserCarList'),
            strtolower('Live.BuyCar'),
            strtolower('Live.RideCar'),
            // 礼物
//            strtolower('Live.GetGiftList'),
//            strtolower('Live.SendGift'),
        );
        if(in_array($service, $goApiList)){
            return true;
        }
        return false;
    }
    
}
