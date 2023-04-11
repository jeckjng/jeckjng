<?php

//引入PHPExcel文件
require EXTEND_PATH .'phpexcel/Classes/PHPExcel.php';

class UtilPhpexcel{

    /*
     * 获取列类型： ABCDEFGHIJKLMNOPQRSTUVWXYZ
     * */
    public static function row_type(){
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $row_type = [];
        for ($i = 0; $i < 26; $i++){
            $row_type[$i+1] = substr($chars, $i, 1);;
        }
        return $row_type;
    }

    /*
    * 获取颜色
    * */
    public static function get_color($color=''){
        if(strlen($color)==4 || strlen($color)==3){
            $new_color = '';
            for ($i = 0; $i < strlen($color); $i++){
                $new_color .= substr($color, $i, 1).substr($color, $i, 1);
            }
            $color = trim($new_color,'##');
        }
        return trim($color,'#');
    }

    /**
     * 导出到Excel 格式美观导出
     *
     * 使用示例
     *
     * $data = array(
     *     array('id' => 1, 'name' => 'Jack', 'age' => 18),
     *     array('id' => 2, 'name' => 'Mary', 'age' => 20),
     *     array('id' => 3, 'name' => 'Ethan', 'age' => 34),
     * );
     * $map = array(
     *     'title'=>array('id' => '编号',
     *          'name' => '姓名',
     *          'age' => '年龄',
     *      )
     * );
     * $file = 'user' . date('Y-m-d');
     * $excel = new \PHPExcel\Excel();
     * $excel->exportExcel($data, $map, $file, '用户信息');
     *
     * @param array $data 需要导出的数据
     * @param array $map 格题、数据格式、数字样式
     *      array(
     *              'title' =>array('id'=>'编号','name'=>'姓名'),
     *              'dataType' =>array('name'=>\PHPExcel_Cell_DataType::TYPE_STRING),
     *              'numberFormat' =>array('created_at' => \PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY),
     *      )
     * @param string $filename 下载显示的默认文件名
     * @param string $title 工作表名称
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public static function export_excel_v1($data, $header = array(), $filename = '', $return_url=false, $title = 'Worksheet')
    {
        if (!is_array($data)) {
            return;
        }
        if (count($data) < 1) {
            return;
        }

        //实例化工作簿对象
        $objPHPExcel = new \PHPExcel();
        //获取活动工作表
        $objActSheet = $objPHPExcel->getActiveSheet();
        //设置工作表的标题
        $objActSheet->setTitle($title);

        $row_type = self::row_type();
        //第一行为标题
        $col = 0;

        foreach ($data[0] as $key => $value) {
            if (isset($key, $header['title'][$key])) {
                $title = $header['title'][$key];
            } else {
                $title = $key;
            }

            $title=explode(":",$title);

            $objActSheet->getCellByColumnAndRow($col, 1)->setValue($title[0]);
            if(isset($title[1])){
                $objActSheet->getColumnDimensionByColumn($col)->setWidth($title[1]);
            }else{
                $objActSheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            }
            $objActSheet->getStyle($row_type[$col+1].'1')->getFont()->setBold(true)
                ->setName('Verdana')
                ->setSize(10)
                ->getColor()->setRGB('23262e');
            $col++;
        }

        //第2行开始是内容
        $row = 2;
        foreach ($data as $v) {
            //第一列序号
            //$objActSheet->getCellByColumnAndRow(0,$row)->setValue($row-1);

            $col = 0;
            foreach ($v as $key => $value) {

                $value_arr=explode("color:",$value);
                if(isset($value_arr[1])){
                    $objActSheet->getStyle($row_type[$col+1].$row)->getFont()->getColor()->setRGB(self::get_color($value_arr[1]));
                }

                $value=" ".$value_arr[0];
                if (isset($key, $header['dataType'][$key])) {
                    $pDataType = $header['dataType'][$key];
                    $objActSheet->getCellByColumnAndRow($col, $row)
                        ->setValueExplicit($value, $pDataType,'s');
                } else {
                    $objActSheet->getCellByColumnAndRow($col, $row,'s')
                        ->setValue($value);
                }

                if (isset($key, $header['numberFormat'][$key])) {
                    $numberFormat = $header['numberFormat'][$key];
                } else {
                    $numberFormat = \PHPExcel_Style_NumberFormat::FORMAT_GENERAL;
                }

                $objActSheet->getStyleByColumnAndRow($col, $row)
                    ->getNumberFormat()
                    ->setFormatCode($numberFormat);

                $col++;
            }
            $row++;
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        if (empty($filename)) {
            $filename = date('YmdHis');
        }

        if (strtolower(substr($filename, -4)) != '.xls') {
            $filename .= '.xls';
        }

        if($return_url === true){
            $dir_path = RUNTIME_PATH.'excel/';
            if(!is_dir($dir_path)){
                mkdir($dir_path,0777);
            }
            $excelPath = $dir_path."{$filename}";
            $objWriter->save($excelPath);
            return $excelPath;
        }

        //也可以浏览器输出
        header('Content-Type: application/vnd.ms-execl');
        header('Content-Disposition: attachment;filename=' . $filename);
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

    /**导出Excel 表格 快速导出
     * @param $expTitle 名称
     * @param $expCellName 参数
     * @param $expTableData 内容
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public static function export_excel_v2($data, $header = array(), $filename = '', $return_url=false, $title = 'Worksheet')
    {
        $expTableData = $data;
        $row_type = self::row_type();
        $cellName = array_slice($row_type,0, count($header['title']));
        $expCellName = array();
        foreach ($header['title'] as $key=>$val){
            $temp = array();
            $title_arr = explode(":",$val);
            array_push($temp,$key);
            array_push($temp,$title_arr[0]);
            $expCellName[] = $temp;
        }

        $cellNum = count($expCellName);
        $dataNum = count($data);

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        for($i=0;$i<$cellNum;$i++){
//            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
            $objPHPExcel->getActiveSheet(0)->setCellValueExplicit($cellName[$i].'1', $expCellName[$i][1],'s');
        }

        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $value = $expTableData[$i][$expCellName[$j][0]];
                $value_arr = explode("color:",$value);
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+2), $value_arr[0]);
            }
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        if($return_url === true){
            $dir_path = RUNTIME_PATH.'excel/';
            if(!is_dir($dir_path)){
                mkdir($dir_path,0777);
            }
            $excelPath = $dir_path.$filename.".xls";
            $objWriter->save($excelPath);
            return $excelPath;
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$filename.'.xls"');
        header("Content-Disposition:attachment;filename=$filename.xls");//attachment新窗口打印inline本窗口打印
        $objWriter->save('php://output');
        exit;
    }

}
