<?php
namespace Admin\Model;

use Admin\Model\AdminModelBaseModel;

class MenuAuthRuleActionModel extends AdminModelBaseModel {

    public $table_name = 'menu_auth_rule_action';

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
     * 保存执行成功的sql语句（menu 和 auth_rule)
     * $menu_type $menu_type
     * */
    public function addAll($sql_data = array(), $menu_type){
        // 是否记录菜单操作sql
        if(getKvconfigVal(1, 'menu_auth_ruleaction') != '1'){
            return 'menu_auth_ruleaction not 1';
        }
        $insert_data = array();
        $operated_by = get_current_admin_user_login();
        foreach ($sql_data as $key=>$val){
            if(strpos($val['action_sql'],'SELECT') === 0){
                continue;
            }

            $insert_data[] = array(
                'operated_by' => $operated_by,
                'action_sql' => trim($val['action_sql']),
                'action_type' => intval($val['action_type']),  // 操作类型：1.INSERT，2.UPDATE，3.DELETE
                'table_name' => trim($val['table_name']),
                'menu_type' => intval($menu_type),   // 菜单类型：1.后台菜单，2.前端菜单
            );
        }
        if(count($insert_data) <= 0){
            return 'count 0';
        }
        $res = M($this->table_name)->addAll($insert_data);
        return [$insert_data, $res];
    }

}
