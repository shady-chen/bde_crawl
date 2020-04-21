<?php


namespace app\admin\controller;
use think\PHPExcel;

/**
 * Class Customer
 * @package app\admin\controller
 */
class Customer extends Base
{


    public function index()
    {
        $map = [];
        //国家
        if(isset($_GET['country']) && !empty($_GET['country']))
        {
            $map['country'] = ['like',"%{$_GET['country']}%"];
        }
        //人名
        if(isset($_GET['username']) && !empty($_GET['username']))
        {
            $map['all_name'] = ['like',"%{$_GET['username']}%"];
        }
        //电话
        if(isset($_GET['phone']) && !empty($_GET['phone']))
        {
            $map['phone'] = ['like',"%{$_GET['phone']}%"];
        }
        $data = db('customer')->where($map)->paginate(10,false,['query'=>$_GET]);
        $this->assign('data',$data);
        $this->assign('page',$data->render());
        return $this->fetch();
    }

    public function test()
    {
        $list = db('customer')->select();
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()->setCreator("ctos")
            ->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

        //设置多少坚
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(8);


        //设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);

        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(20);

        //set font size bold
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle('A2:I2')->getFont()->setBold(true);

        //设置表头 水平垂直 加粗字体
        $objPHPExcel->getActiveSheet()->getStyle('A2:I2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:I2')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        //设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('I')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        //合并cell
        $objPHPExcel->getActiveSheet()->mergeCells('A1:J1');

        // set table header content
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '顾客数据汇总  时间:' . date('Y-m-d H:i:s'))
            ->setCellValue('A2', '序号')
            ->setCellValue('B2', '姓名')
            ->setCellValue('C2', '性别')
            ->setCellValue('D2', 'email')
            ->setCellValue('E2', '电话')
            ->setCellValue('F2', '国家')
            ->setCellValue('G2', '城市')
            ->setCellValue('H2', '住址')
            ->setCellValue('I2', '货币');


        // Miscellaneous glyphs, UTF-8
        for ($i = 0; $i < count($list) - 1; $i++) {
            $objPHPExcel->getActiveSheet(0)->setCellValue('A' . ($i + 3), $list[$i]['id']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('B' . ($i + 3), $list[$i]['first_name'].$list[$i]['last_name']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('C' . ($i + 3), $list[$i]['rbsex']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('D' . ($i + 3), $list[$i]['email']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('E' . ($i + 3), $list[$i]['phone']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('F' . ($i + 3), $list[$i]['country']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('G' . ($i + 3), $list[$i]['city']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('H' . ($i + 3), $list[$i]['address']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('I' . ($i + 3), $list[$i]['currency']);
            //$objPHPExcel->getActiveSheet()->getStyle('A'.($i+3).':J'.($i+3))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //$objPHPExcel->getActiveSheet()->getStyle('A'.($i+3).':J'.($i+3))->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getRowDimension($i + 3)->setRowHeight(16);
        }


        //  sheet命名
        $objPHPExcel->getActiveSheet()->setTitle('customers_sheet');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // excel头参数
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="customers_sheet(' . date('Ymd-His') . ').xls"');  //日期为文件名后缀
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式

        $objWriter->save('php://output');
    }



}