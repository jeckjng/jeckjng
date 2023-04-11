<?php

/**
 * Menu(前台菜单管理)
 */
namespace Admin\Controller;
use Admin\Model\MenuAuthRuleActionModel;
use Common\Controller\AdminbaseController;
class ReceptionmeunController extends AdminbaseController{

    /**
     *  显示菜单
     */
    public function index() {

        $result = M('reception_meun')->where(array('id'=>array('neq',18)))->select();
        import("Tree");
        $tree = new \Tree();

        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $newmenus=array();
        foreach ($result as $m){
            $newmenus[$m['id']]=$m;

        }
        foreach ($result as $n=> $r) {

            $result[$n]['level'] = $this->_get_level($r['id'], $newmenus);
            $result[$n]['parentid_node'] = ($r['parentid']) ? ' class="child-of-node-' . $r['parentid'] . '"' : '';

            $result[$n]['str_manage'] = '<a href="' . U("Receptionmeun/add", array("parentid" => $r['id'], "menuid" => I("get.menuid"))) . '">'.添加子菜单.'</a> | <a target="_blank" href="' . U("Receptionmeun/edit", array("id" => $r['id'], "menuid" => I("get.menuid"))) . '">'.L('EDIT').'</a>  ';
            $result[$n]['status'] = $r['status'] ? L('DISPLAY') : L('HIDDEN');

        }

        $tree->init($result);
        $str = "<tr id='node-\$id' \$parentid_node>
			
					<td>\$id</td>
					<td>\$spacer\$name</td>
				    <td>\$status</td>
					<td>\$str_manage</td>
				</tr>";
        $categorys = $tree->get_tree(0, $str);
        $this->assign("categorys", $categorys);
        $this->display();
    }

    /**
     * 获取菜单深度
     * @param $id
     * @param $array
     * @param $i
     */
    protected function _get_level($id, $array = array(), $i = 0) {

        if ($array[$id]['parentid']==0 || empty($array[$array[$id]['parentid']]) || $array[$id]['parentid']==$id){
            return  $i;
        }else{
            $i++;
            return $this->_get_level($array[$id]['parentid'],$array,$i);
        }

    }

    /**
     *  添加
     */
    public function add() {
        import("Tree");
        $tree = new \Tree();
        $parentid = intval(I("get.parentid"));
        $result = M('reception_meun')->select();
        foreach ($result as $r) {
            $r['selected'] = $r['id'] == $parentid ? 'selected' : '';
            $array[] = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $select_categorys = $tree->get_tree(0, $str);
        $this->assign("select_categorys", $select_categorys);
        $this->display();
    }

    /**
     *  添加
     */
    public function add_post() {
        if (IS_POST) {
            $model = M('reception_meun');
            if ($model->create()) {
                $model->addtime = time();
                if ($model->add()!==false) {
                    $action="添加前台菜单";
                    setAdminLog($action);

                    // 保存操作sql，用于同步菜单
                    $menu_res = MenuAuthRuleActionModel::getInstance()->addAll(array(
                        array(
                            'action_sql' => $model->getLastSql(),
                            'action_type' => 1, // 操作类型：1.INSERT，2.UPDATE，3.DELETE
                            'table_name' => 'reception_meun',
                        ),
                    ), 2);

                    $this->success("添加成功！");
                } else {
                    $this->error("添加失败！");
                }
            } else {
                $this->error($this->menu_model->getError());
            }
        }
    }
    /**
     *  编辑
     */
    public function edit() {
        import("Tree");
        $tree = new \Tree();
        $id = intval(I("get.id"));
        $rs = M('reception_meun')->where(array("id" => $id))->find();
        $result = M('reception_meun')->select();
        foreach ($result as $r) {
            $r['selected'] = $r['id'] == $rs['parentid'] ? 'selected' : '';
            $array[] = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $select_categorys = $tree->get_tree(0, $str);
        $this->assign("data", $rs);
        $this->assign("select_categorys", $select_categorys);
        $this->display();
    }

    /**
     *  编辑
     */
    public function edit_post() {
        if (IS_POST) {
            $model = M('reception_meun');
            if ($model->create()) {
                if ($model->save() !== false) {
                    $action="编辑前台菜单：{$_POST['id']}";
                    setAdminLog($action);

                    // 保存操作sql，用于同步菜单
                    $menu_res = MenuAuthRuleActionModel::getInstance()->addAll(array(
                        array(
                            'action_sql' => $model->getLastSql(),
                            'action_type' => 2, // 操作类型：1.INSERT，2.UPDATE，3.DELETE
                            'table_name' => 'reception_meun',
                        ),
                    ), 2);
                    $this->success("更新成功！");
                } else {
                    $this->error("更新失败！");
                }
            } else {
                $this->error($this->menu_model->getError());
            }
        }
    }

}

