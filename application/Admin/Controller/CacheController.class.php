<?php

/**
 * 管理员日志
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

use Admin\Model\CommonModel;
use Common\Controller\CustRedis;

class CacheController extends AdminbaseController {

    private $string_type_list = array(
        '1' => '获取值',
        '2' => '获取值的长度',
        '3' => '查看是否存在',
    );

    private $hash_type_list = array(
        '1' => '查看是否存在',
        '2' => '获取指定字段的值',
        '3' => '获取所有字段和值',
        '4' => '获取所有字段',
        '5' => '获取字段的数量',
        '6' => '获取所有值',
    );

    private $list_type_list = array(
        '1' => '通过索引获取元素',
        '2' => '获取列表长度',
        '3' => '获取区间内所有元素',
    );

    public function redis(){
        $param = I('param.');
        if(IS_POST){
            switch ($param['cache_type']){
                case 'string': // 字符串
                    if(!isset($param['string_key']) || !$param['string_key']){
                        $this->out_put('',1,'请输入 string key');
                    }
                    $data = $this->redis_string($param['string_key'], $param['string_type']);
                    break;
                case 'hash': // 哈希
                    if(!isset($param['hash_key']) || !$param['hash_key']){
                        $this->out_put('',1, '请输入 hash key');
                    }
                    $data = $this->redis_hash($param['hash_key'], $param['hash_element_key'], $param['hash_type']);
                    break;
                case 'list': // 列表
                    if(!isset($param['list_key']) || !$param['list_key']){
                        $this->out_put('',1, '请输入 list key');
                    }
                    if($param['list_index'] == '' && ($param['list_start'] == '' || $param['list_end'] == '')){
                        $this->out_put('',1, '请输入 下标或者下标区间');
                    }
                    $data = $this->redis_list($param['list_key'], $param['list_index'], $param['list_start'], $param['list_end'], $param['list_type']);
                    break;
                case 'set': // 集合
                    $data = '';
                    break;
                case 'sorted_set': // 有序集合
                    $data = '';
                    break;
                default:
                    $data = '';
            }
            $this->out_put($data, 200,'success');
        }

        $this->assign('string_type_list', $this->string_type_list);
        $this->assign('hash_type_list', $this->hash_type_list);
        $this->assign('list_type_list', $this->list_type_list);
    	$this->display();
    }

    /*
    * redis string 缓存查询
    * */
    public function redis_string($key, $type = '1'){
        switch ($type){
            case '1': // 获取指定 key 的值。
                $data = CustRedis::getInstance()->get($key);
                break;
            case '2': // 返回 key 所储存的字符串值的长度。。
                $data = CustRedis::getInstance()->strlen($key);
                break;
            case '3': // 查看指定的字段是否存在。
                $data = CustRedis::getInstance()->exists($key);
                break;
            default:
                $data = '';
        }

        return $data;
    }

    /*
     * redis hash 缓存查询
     * */
    public function redis_hash($key, $hash_key, $type = '2'){
        switch ($type){
            case '1': // 查看哈希表 key 中，指定的字段是否存在。
                $data = CustRedis::getInstance()->hExists($key, $hash_key);
                break;
            case '2': // 获取存储在哈希表中指定字段的值。
                $data = CustRedis::getInstance()->hGet($key, $hash_key);
                break;
            case '3': // 获取在哈希表中指定 key 的所有字段和值
                $data = CustRedis::getInstance()->hGetAll($key);
                $data = json_encode($data, JSON_UNESCAPED_UNICODE);
                break;
            case '4': // 获取所有哈希表中的字段
                $data = CustRedis::getInstance()->hKeys($key);
                break;
            case '5': // 获取哈希表中字段的数量
                $data = CustRedis::getInstance()->hLen($key);
                break;
            case '6': // 获取哈希表中所有值。
                $data = CustRedis::getInstance()->hLen($key);
                break;
            default:
                $data = '';
        }

        return $data;
    }

    /*
     * redis list 缓存查询
     * */
    public function redis_list($key, $list_index, $list_start, $list_end, $type = '1'){
        switch ($type){
            case '1': // 通过索引获取列表中的元素
                $data = CustRedis::getInstance()->lIndex($key, intval($list_index));
                break;
            case '2': // 获取列表长度。
                $data = CustRedis::getInstance()->lLen($key);
                break;
            case '3': // 获取列表区间内所有元素
                $list_start = intval($list_start);
                $list_end = intval($list_end);
                $list_start = $list_start >= 0 ? $list_start : 0;
                $list_end = $list_end >= -1 ? $list_end : -1;
                $data = CustRedis::getInstance()->lRange($key, intval($list_start), intval($list_end));
                $data = json_encode($data, JSON_UNESCAPED_UNICODE);
                break;
            default:
                $data = '';
        }

        return $data;
    }


}
