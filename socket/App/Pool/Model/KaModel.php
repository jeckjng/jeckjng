<?php
/**
 * Created by PhpStorm.
 * User: nms
 * Date: 2021/3/13
 * Time: 21:17
 */

namespace App\Model;

use  App\Model\Table\kaTable;
use EasySwoole\ORM\DbManager;

class  KaModel
{
    //获取CODE
    public function getCode($token)
    {
        $code = DbManager::getInstance()->invoke(function ($client) use ($token) {
            return kaTable::invoke($client)->field('lhh')->get(['token' => $token]);
        });
        if ($code) {
            return [200, $code];
        }
        return [400, []];
    }

}