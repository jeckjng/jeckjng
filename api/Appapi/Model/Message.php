<?php

class Model_Message extends PhalApi_Model_NotORM {
	/* 信息列表 */
	public function getList($uid,$p) {
        if($p<1){
            $p=1;
        }
		$pnum=50;
		$start=($p-1)*$pnum;
        
		$list=DI()->notorm->pushrecord
                    ->select('content,addtime')
                    ->where("touid='' or( touid!='' and (touid = '{$uid}' or touid like '{$uid},%' or touid like '%,{$uid},%' or touid like '%,{$uid}') )")
                    ->order('addtime desc')
                    ->limit($start,$pnum)
                    ->fetchAll();

		return $list;
	}

    /* 游戏数据写入redis  */
    public function pushgamedata($data) {
        $nums=DI()->redis->lPush('gamenews_queue',json_encode($data));
        DI()->redis->lPush('gamenews_queues',json_encode($data));
        return $nums;
    }

}
