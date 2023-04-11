<?php
namespace Admin\Controller;
use Think\Controller;

class  DictController extends Controller
{

    public function index()
    {

        $path = dirname(__FILE__) . '/../../../data/conf/db.php';
        $dbconfig = include $path;
        $parm = $_REQUEST['tbname'];
        $model = M();
        $db_name = $dbconfig['DB_NAME'];
        $db_rst = $model->query('SHOW TABLES');
        $tables = [];
        foreach ($db_rst as $key => $value) {
            // $db_rst[$key]['name'] = $value["tables_in_{$db_name}"];
            $tables[]['TABLE_NAME'] = $value["tables_in_{$db_name}"];

        }

        foreach ($tables as $tableKey => $tableValue){
            $table_result = $model->query("SELECT * FROM  information_schema.TABLES WHERE table_name = '{$tableValue['TABLE_NAME']}'  AND table_schema = '{$db_name}'" );

            foreach ($table_result as  $table_resultKey => $table_resultValue){
                $tables[$tableKey]['TABLE_COMMENT'] = $table_resultValue['TABLE_COMMENT'];
            }
            $fields = [];
            $field_result = $model->query("SELECT * FROM  information_schema.COLUMNS WHERE table_name = '{$tableValue['TABLE_NAME']}'  AND table_schema = '{$db_name}'" );
            foreach ($field_result as  $field_resultKey => $field_resultValue){
                $fields[] = $field_resultValue;
            }
            $tables[$tableKey]['COLUMN'] = $fields;
        }
        // echo '<pre>';
        //  var_dump($tables);exit;
        $html = '';
        foreach($tables as $k => $v)
        {
            $html .= '<table border="1" cellspacing="0" cellpadding="0" align="center">';
            $html .= '<caption>表名：' . $v['TABLE_NAME'] . ' ' . $v['TABLE_COMMENT'] . '</caption>';
            $html .= '<tbody><tr><th>长度</th><th>字段名</th><th>数据类型</th><th>默认值</th><th>允许非空</th><th>自动递增</th><th>备注</th></tr>';
            $html .= '';
            foreach($v['COLUMN'] AS $fk =>  $f)
            {   $html .= '<td class="c1">' . $fk . '</td>';
                $html .= '<td class="c1">' . $f['column_name'] . '</td>';
                $html .= '<td class="c2">' . $f['column_type'] . '</td>';
                $html .= '<td class="c3">' . $f['column_default'] . '</td>';
                $html .= '<td class="c4">' . $f['is_nullable'] . '</td>';
                $html .= '<td class="c5">' . ($f['extra'] == 'auto_increment'?'是':' ') . '</td>';
                $html .= '<td class="c6">' . $f['column_comment'] . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table></p>';
        }

        echo '<html>
          <meta charset="utf-8">
          <title>自动生成数据字典</title>
          <style>
            body,td,th {font-family:"宋体"; font-size:12px;}
            table,h1,p{width:960px;margin:0px auto;}
            table{border-collapse:collapse;border:1px solid #CCC;background:#efefef;}
            table caption{text-align:left; background-color:#fff; line-height:2em; font-size:14px; font-weight:bold; }
            table th{text-align:left; font-weight:bold;height:26px; line-height:26px; font-size:12px; border:1px solid #CCC;padding-left:5px;}
            table td{height:20px; font-size:12px; border:1px solid #CCC;background-color:#fff;padding-left:5px;}
            .c1{ width: 150px;}
            .c2{ width: 150px;}
            .c3{ width: 80px;}
            .c4{ width: 100px;}
            .c5{ width: 100px;}
            .c6{ width: 300px;}
     </style>
  <body>';
        echo '<h1 style="text-align:center;">'.$db_name.'数据字典</h1>';
        echo '<p style="text-align:center;margin:20px auto;">生成时间：' . date('Y-m-d H:i:s') . '</p>';
        echo $html;
        echo '<p style="text-align:left;margin:20px auto;">总共：' . count($tables) . '个数据表</p>';
        echo '</body></html>';

    }

    /* public function info()
     {
         $name = $_REQUEST['name'];
         $model = M();
         $db_rst = $model->query("SHOW CREATE TABLE  $name ");
         echo '<pre>';
         var_dump($db_rst);
         exit;
         $this->assign('lists', $db_rst);
     }*/



}