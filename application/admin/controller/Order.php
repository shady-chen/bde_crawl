<?php


namespace app\admin\controller;
use think\PHPExcel;

class Order extends Base
{
    private $search_data = null;

    /**
     * 订单列表的页面
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $map = [];
        //域名
        if (isset($_GET['domain']) && !empty($_GET['domain'])) {
            $map['a.data_from'] = ['=', $_GET['domain']];
        }
        //国家
        if (isset($_GET['country']) && !empty($_GET['country'])) {
            $map['b.country'] = ["like", "%" . $_GET['country'] . "%"];
        }
        //人名
        if (isset($_GET['username']) && !empty($_GET['username'])) {
            $map['b.all_name'] = ["like", "%" . $_GET['username'] . "%"];
        }
        //产品
        if (isset($_GET['product_name']) && !empty($_GET['product_name'])) {
            $map['a.product_keyword'] = ["like", "%" . $_GET['product_name'] . "%"];
        }
        //订单状态
        if (isset($_GET['state']) && !empty($_GET['state'])) {
            $map['a.order_state'] = ["=", $_GET['state']];
        }
        //付款方式
        if (isset($_GET['pay_way']) && !empty($_GET['pay_way'])) {
            $map['a.pay_way'] = ["=", $_GET['pay_way']];
        }

        //时间
        $result_by_start_time = "2000-01-01";
        $result_by_end_time = "2030-01-01";
        if (isset($_GET['start_time']) && !empty($_GET['start_time'])) {
            $result_by_start_time = $_GET['start_time'];
        }
        if (isset($_GET['end_time']) && !empty($_GET['end_time'])) {
            $result_by_end_time = $_GET['end_time'];
        }
        $map['a.order_time'] = ["between time", [$result_by_start_time, $result_by_end_time]];

        $data = db('order')
            ->alias("a")
            ->join('customer b', 'a.customer_id = b.id')
            ->order('a.create_time desc')
            ->where($map)
            ->paginate(10, false, ['type' => 'BootstrapDetail', "query" => $_GET]);
        $page = $data;
        $data = $data->items();
        //保存到类的属性中，用于管理员导出数据时使用，避免再次查询数据库
        //$this->setData($data);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['product'] = null;
            $data[$i]['product'] = json_decode($data[$i]['shopping_cart'], true);
        }
        $this->assign('data', $data);
        $this->assign('page', $page->render());
        $website_list = db('website_list')->select();
        $this->assign('website_list', $website_list);
        return $this->fetch();
    }


    //导出成Excel
    public function OutPutBeExcel()
    {

        //加载导出类
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        //获取数据
        $map = [];
        //域名
        if (isset($_GET['domain']) && !empty($_GET['domain'])) {
            $map['a.data_from'] = ['=', $_GET['domain']];
        }
        //国家
        if (isset($_GET['country']) && !empty($_GET['country'])) {
            $map['b.country'] = ["like", "%" . $_GET['country'] . "%"];
        }
        //人名
        if (isset($_GET['username']) && !empty($_GET['username'])) {
            $map['b.all_name'] = ["like", "%" . $_GET['username'] . "%"];
        }
        //产品
        if (isset($_GET['product_name']) && !empty($_GET['product_name'])) {
            $map['a.product_keyword'] = ["like", "%" . $_GET['product_name'] . "%"];
        }
        //订单状态
        if (isset($_GET['state']) && !empty($_GET['state'])) {
            $map['a.order_state'] = ["=", $_GET['state']];
        }
        //付款方式
        if (isset($_GET['pay_way']) && !empty($_GET['pay_way'])) {
            $map['a.pay_way'] = ["=", $_GET['pay_way']];
        }

        //时间
        $result_by_start_time = "2000-01-01";
        $result_by_end_time = "2030-01-01";
        if (isset($_GET['start_time']) && !empty($_GET['start_time'])) {
            $result_by_start_time = $_GET['start_time'];
        }
        if (isset($_GET['end_time']) && !empty($_GET['end_time'])) {
            $result_by_end_time = $_GET['end_time'];
        }
        $map['a.order_time'] = ["between time", [$result_by_start_time, $result_by_end_time]];

        $list = db('order')
            ->alias("a")
            ->join('customer b', 'a.customer_id = b.id')
            ->order('a.create_time desc')
            ->where($map)
            ->select();

        //开始写入Excel
        $objPHPExcel->getProperties()->setCreator("ctos")
            ->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

        //设置多少坚
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);//序号
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(18);//订单号
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);// 姓名
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(3);//性别
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(11);//email
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);//地址
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(35);//产品
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);// 产品地址
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(6);//总价
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(8);//下单时间


        //设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(34);

        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(80);//产品
        //set font size bold
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(12);
        $objPHPExcel->getActiveSheet()->getStyle('A2:J2')->getFont()->setBold(true);

        //设置表头 水平垂直 加粗字体
        $objPHPExcel->getActiveSheet()->getStyle('A2:J2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:J2')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        //设置水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('I')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('J')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setWrapText(true);

        //合并cell
        $objPHPExcel->getActiveSheet()->mergeCells('A1:J1');

        // set table header content
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '订单数据汇总  时间:' . date('Y-m-d H:i:s'))
            ->setCellValue('A2', '序号')
            ->setCellValue('B2', '订单号')
            ->setCellValue('C2', '姓名')
            ->setCellValue('D2', '性别')
            ->setCellValue('E2', 'email')
            ->setCellValue('F2', '地址')
            ->setCellValue('G2', '产品名')
            ->setCellValue('H2', '产品图片名称')
            ->setCellValue('I2', '总价格')
            ->setCellValue('J2', '下单时间');


        // Miscellaneous glyphs, UTF-8
        for ($i = 0; $i < count($list); $i++) {

            $objPHPExcel->getActiveSheet(0)->setCellValue('A' . ($i + 3), $i+1);
            $objPHPExcel->getActiveSheet(0)->setCellValue('B' . ($i + 3), $list[$i]['order_id']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('C' . ($i + 3), $list[$i]['first_name'].$list[$i]['last_name']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('D' . ($i + 3), $list[$i]['rbsex']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('E' . ($i + 3), $list[$i]['email']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('F' . ($i + 3), $list[$i]['country'] . $list[$i]['city'] . $list[$i]['address']);
            $product_name = "";
            $url = "";
            $product = json_decode($list[$i]['shopping_cart'], true);
            foreach ($product as $k=>$v){
                $product_name .= $v['productname']."*" .$v['quantity']."\n";
                $url .= $v['imgurl']."\n";
            }

            $objPHPExcel->getActiveSheet(0)->setCellValue('G' . ($i + 3), $product_name);
            $objPHPExcel->getActiveSheet(0)->setCellValue('H' . ($i + 3), $url);
            $objPHPExcel->getActiveSheet(0)->setCellValue('I' . ($i + 3), $list[$i]['total']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('J' . ($i + 3), $list[$i]['order_time']);
            //$objPHPExcel->getActiveSheet()->getStyle('A'.($i+3).':J'.($i+3))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //$objPHPExcel->getActiveSheet()->getStyle('A'.($i+3).':J'.($i+3))->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getRowDimension($i + 3)->setRowHeight(20);
           // $objPHPExcel->getActiveSheet()->getDefaultRowDimension($i + 3)->setRowHeight (-1);
        }

        //  sheet命名
        $objPHPExcel->getActiveSheet()->setTitle('order_sheet');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // excel头参数
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Order_sheet(' . date('Ymd-His') . ').xls"');  //日期为文件名后缀
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式

        $objWriter->save('php://output');


    }




    public function outPutBeTxt()
    {

        $map = [];
        //域名
        if (isset($_GET['domain']) && !empty($_GET['domain'])) {
            $map['a.data_from'] = ['=', $_GET['domain']];
        }
        //国家
        if (isset($_GET['country']) && !empty($_GET['country'])) {
            $map['b.country'] = ["like", "%" . $_GET['country'] . "%"];
        }
        //人名
        if (isset($_GET['username']) && !empty($_GET['username'])) {
            $map['b.all_name'] = ["like", "%" . $_GET['username'] . "%"];
        }
        //产品
        if (isset($_GET['product_name']) && !empty($_GET['product_name'])) {
            $map['a.product_keyword'] = ["like", "%" . $_GET['product_name'] . "%"];
        }
        //订单状态
        if (isset($_GET['state']) && !empty($_GET['state'])) {
            $map['a.order_state'] = ["=", $_GET['state']];
        }
        //付款方式
        if (isset($_GET['pay_way']) && !empty($_GET['pay_way'])) {
            $map['a.pay_way'] = ["=", $_GET['pay_way']];
        }

        //时间
        $result_by_start_time = "2000-01-01";
        $result_by_end_time = "2030-01-01";
        if (isset($_GET['start_time']) && !empty($_GET['start_time'])) {
            $result_by_start_time = $_GET['start_time'];
        }
        if (isset($_GET['end_time']) && !empty($_GET['end_time'])) {
            $result_by_end_time = $_GET['end_time'];
        }
        $map['a.order_time'] = ["between time", [$result_by_start_time, $result_by_end_time]];

        $list = db('order')
            ->alias("a")
            ->join('customer b', 'a.customer_id = b.id')
            ->order('a.create_time desc')
            ->where($map)
            ->select();

        //txt文件内容 = 订单号#姓名#邮箱#国家#产品名#产品地址#下单时间
        $txt_content = "";

        for ($i = 0; $i < count($list); $i++) {
            $txt_content .= "{$list[$i]['order_id']}#{$list[$i]['all_name']}#{$list[$i]['email']}#{$list[$i]['order_id']}#{$list[$i]['country']}#";
            $product_str = "";
            $product = json_decode($list[$i]['shopping_cart'], true);
            foreach ($product as $k=>$v){
                $product_str .=  $v['productname'] . "," . $v['imgurl'] . "||";
            }
            $txt_content .= $product_str . "#" .$list[$i]['order_time'] ."\n" ;
        }

        //第一步：处理中文文件名：
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $filename = time().".txt";
        $encoded_filename = urlencode($filename);
        $encoded_filename = str_replace("+","%20",$encoded_filename);
        //第二步：生成TXT文件
        header("Content-Type: application/octet-stream");
        if (preg_match("/MSIE/",$_SERVER['HTTP_USER_AGENT'])){
            header('Content-Disposition:attachment;filename="'.$encoded_filename.'"');
        }elseif(preg_match("/Firefox/",$_SERVER['HTTP_USER_AGENT'])){
            header('Content-Disposition:attachment;filename*="utf8'.$filename.'"');
        }else{
            header('Content-Disposition:attachment;filename="'.$filename.'"');
        }
        //第三步：输出内容
        echo $txt_content;
        exit();

    }

}