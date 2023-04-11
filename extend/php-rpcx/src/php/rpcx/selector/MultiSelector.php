<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/11/3
 * Time: 16:54
 */

namespace php\rpcx\selector;

use php\rpcx\Selector;

class MultiSelector implements Selector
{
    private $_clients;
    private $_servers;

    public function __construct()
    {
        
    }

    public function select($trans, $codec)
    {
        if ($this->_client) {
            return $this->_client;
        }else{
            $this->_client = new Client($trans, $codec);
            return $this->_client;
        }
    }

    public function setClient($client)
    {
        $this->_client = $client;
    }

    public function setSelectMode($selectMode)
    {
    }

    public function allClients($trans, $codec)
    {
        return [$this->_client];
    }

}