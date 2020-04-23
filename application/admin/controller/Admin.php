<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 21:23
 */

namespace app\admin\controller;


use think\Request;

class Admin extends Base
{
    function _initialize()
    {
        if (!$this->isAdmin()) {
            return $this->error('您还没有登录！', '/admin/user/login');
        }
        //parent::_initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * 数据报表
     */
    public function index()
    {
        $data = [];
        $statistics = [
            'domain_amount' => 0,
            'all_website_amount' => 0,
            'all_website_amount_today' => 0,
            'all_website_amount_yesterday' => 0,
            'all_website_success_amount' => 0,
            'all_website_success_amount_today' => 0,
            'all_website_success_amount_yesterday' => 0,
            'all_website_success_money' => 0,
            'all_website_success_money_today' => 0,
            'all_website_success_money_yesterday' => 0,
        ];
        //1找出网站的列表
        $website_list = db('website_list')->where(['state' => 1])->column('domain');
        //去order中找总数量和成功的数量
        for ($i = 0; $i < count($website_list); $i++) {
            //订单总数量
            $total_amount = db('order')->where('data_from', '=', $website_list[$i])->count();
            $total_amount_today = db('order')
                ->whereTime('finished_time', 'today')
                ->where('data_from', '=', $website_list[$i])
                ->count();
            $total_amount_yesterday = db('order')
                ->whereTime('finished_time', 'yesterday')
                ->where('data_from', '=', $website_list[$i])
                ->count();
            //成功订单的数量
            $success_amount = db('order')->where(['data_from' => $website_list[$i], 'order_state' => 'Success'])->count();
            $success_amount_today = db('order')
                ->whereTime('finished_time', 'today')
                ->where(['data_from' => $website_list[$i], 'order_state' => 'Success'])
                ->count();
            $success_amount_yesterday = db('order')
                ->whereTime('finished_time', 'yesterday')
                ->where(['data_from' => $website_list[$i], 'order_state' => 'Success'])
                ->count();
            //成功订单的总金额
            $success_money = db('order')->where(['data_from' => $website_list[$i], 'order_state' => 'Success'])->sum('total');
            $success_money_today = db('order')
                ->whereTime('finished_time', 'today')
                ->where(['data_from' => $website_list[$i], 'order_state' => 'Success'])
                ->sum('total');
            $success_money_yesterday = db('order')
                ->whereTime('finished_time', 'yesterday')
                ->where(['data_from' => $website_list[$i], 'order_state' => 'Success'])
                ->sum('total');
            $data[$i] = [
                'domain' => $website_list[$i],
                'total_amount' => $total_amount,
                'total_amount_today' => $total_amount_today,
                'total_amount_yesterday' => $total_amount_yesterday,
                'success_amount' => $success_amount,
                'success_amount_today' => $success_amount_today,
                'success_amount_yesterday' => $success_amount_yesterday,
                'success_money' => $success_money,
                'success_money_today' => $success_money_today,
                'success_money_yesterday' => $success_money_yesterday,
            ];
            $statistics['all_website_amount'] += $total_amount;
            $statistics['all_website_amount_today'] += $total_amount_today;
            $statistics['all_website_amount_yesterday'] += $total_amount_today;
            $statistics['all_website_success_amount'] += $success_amount;
            $statistics['all_website_success_amount_today'] += $success_amount_today;
            $statistics['all_website_success_amount_yesterday'] += $success_amount_yesterday;
            $statistics['all_website_success_money'] += $success_money;
            $statistics['all_website_success_money_today'] += $success_money_today;
            $statistics['all_website_success_money_yesterday'] += $success_money_yesterday;
            $statistics['domain_amount']++;
        }
        $this->assign('data', $data);
        $this->assign('statistics', $statistics);
        return $this->fetch();
    }



    /**
     * 客户信息的页面
     * @return mixed
     */
    public function onePerson()
    {
        $id = $this->request->param('contentId');
        $appOrder = db('order_content');
        $data = $appOrder->where(['id' => $id])->value('content');
        $data = json_decode($data, true);

        $data['shoppingcart'] = json_decode($data['shoppingcart'], true);

        $this->assign('data', $data);
        return $this->fetch();

    }


    /**
     * down_loan页面
     * @return mixed
     */
    public function down_load()
    {
        return $this->fetch();
    }

    /**
     * 获取下载的数据
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function down_load_data()
    {
        $data = [];

        //总的数量有多少个
        $total_count = db('website_list')->where(['state' => 1])->count();
        //根据上传的分页值取数据
        $NumberOfPages = $this->request->param('nop') ? $this->request->param('nop') : 3;
        //当前是第几页
        $CurrentPage = $this->request->param('cp') ? $this->request->param('cp') : 1;

        $website_list = db('website_list')->where(['state' => 1])->limit(($CurrentPage - 1) * $NumberOfPages, $CurrentPage * $NumberOfPages)->select();

        for ($i = 0; $i < count($website_list); $i++) {
            $domain = $website_list[$i]['domain'];
            $data[] = [
                'domain' => $domain,
                'downloaded' => db('file_name')->where(['belong' => $domain, 'collected' => 1])->count()
            ];
        }
        $response = [
            'data' => $data,
            'total_count' => $total_count,//总条数
            //  'all'=>intval($total_count/$NumberOfPages)==1?1:intval($total_count/$NumberOfPages) + 1,//总页数
            'pageNum' => $CurrentPage,//当前页数
            'totalPage' => count($website_list),//当前页数

        ];
        if ($total_count <= $NumberOfPages) {
            $response['all'] = 1;
        } else {
            if ($total_count % $NumberOfPages == 0) {
                $response['all'] = $total_count / $NumberOfPages;
            } else {
                $response['all'] = intval($total_count / $NumberOfPages) + 1;//总页数
            }

        }

        return $response;
    }


    /**
     * 1:检测目录下有多少文件
     * 2:将目录写入库中
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pingDomain()
    {
        $return_data = ["status" => 0, "count" => 0, 'new_file_count' => 0];
        $domain = $this->request->param('domain');
        $files = $this->getData('http://'.$domain.'/showFiles.php?dir=/' . $domain);
        //var_dump('http://'.$domain.'/showFiles.php?dir=/' . $domain);
       // exit();
        $files = json_decode($files);

        if (is_array($files)) {
            foreach ($files as $key => $value) {
                $number = 11 + strlen($domain) + 1;
                $file_name = substr($value, $number);
                if (!db('file_name')->where(['file_name' => $file_name])->find()) {
                    db('file_name')->insert(['file_name' => $file_name, "belong" => $domain, "create_time" => time()]);
                    $return_data['new_file_count']++;
                }

            }
            $return_data['status'] = 200;
            $return_data['count'] = count($files);
            $return_data['data'] = json_encode($files);
        } else {
            $return_data['status'] = 404;
            $return_data['count'] = 0;
        }
        return json($return_data);
    }

    /**
     * curl
     * @param $url
     * @return bool|string
     */
    public function getData($url)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode === 403 || $httpCode === 404 || $httpCode === 500) {
            return false;
        }
        curl_close($ch);
        if ($output === FALSE) {
            return false;
        } else {
            return $output;
        }

    }


    //down_load的第一步 更新网站列表
    public function getWebSiteList()
    {

        $return_data = [];
        $website_list = file_get_contents("http://us.shopnm.top/showDir.php");
        $website_list = json_decode($website_list, true);
        $insertCount = 0;
        foreach ($website_list as $key => $value) {
            if (!db('website_list')->where(['domain' => substr($value, 11)])->find()) {
                db('website_list')->insert(['domain' => substr($value, 11), "create_time" => time()]);
                $insertCount++;
            } else {

            }
            $return_data[$key] = [
                'domain' => substr($value, 11),
                'count' => 0,
                'downloaded' => 0,
                'new' => 0,
            ];
        }

        $file_count = 0;
        $file_insert_count = 0;
        foreach ($website_list as $key => $value) {
            $file_list = file_get_contents("http://us.shopnm.top/showFiles.php?dir=/" . substr($value, 11));
            $file_list = json_decode($file_list, true);
            $return_data[$key]['count'] = count($file_list);
            foreach ($file_list as $k => $v) {
                $number = 11 + strlen(substr($value, 11)) + 1;
                $file_name = substr($v, $number);
                if (!db('file_name')->where(['file_name' => $file_name])->find()) {
                    db('file_name')->insert(['file_name' => $file_name, "belong" => substr($value, 11), "create_time" => time()]);
                    $file_insert_count++;
                }
                $file_count++;
            }
        }
        //数据库中查看已下载种未下载的数量
        for ($i = 0; $i < count($return_data); $i++) {
            $return_data[$i]['count'] = db('file_name')->where(['belong' => $return_data[$i]['domain']])->count();
            $return_data[$i]['downloaded'] = db('file_name')->where(['belong' => $return_data[$i]['domain'], 'collected' => 1])->count();
            $return_data[$i]['new'] = db('file_name')->where(['belong' => $return_data[$i]['domain'], 'collected' => 0])->count();
        }

        return json($return_data);
        //到此目录已完成
    }


    //down_load的第二步 下载网站的内容
    public function getFileContent()
    {
        $return_data = ['count' => 0, 'download' => 0];
        $url = $this->request->param('url');

        if ($url == "all") {
            $file_name_list = db('file_name')->where(['collected' => 0])->select();
        } else {
            $file_name_list = db('file_name')->where(['collected' => 0, 'belong' => $url])->select();
        }
        $data_count = count($file_name_list);
        $insert_count = 0;
        foreach ($file_name_list as $key => $value) {

            $content = $this->getData("http://".$value['belong']."/ordersdb/" . $value['belong'] . "/" . $value['file_name']);
            if ($content) {
                if (!db('order_content')->where(['file_name' => $value['file_name']])->find()) {
                    db('order_content')->insert(['file_name' => $value['file_name'], "belong" => $value['belong'], "content" => $content, "create_time" => time()]);
                    db('file_name')->where(['file_name' => $value['file_name']])->update(['collected' => 1]);
                    $insert_count++;
                }
            }
        }
        $return_data['count'] = $data_count;
        $return_data['download'] = $insert_count;
        $return_data['status'] = 200;
        $this->classifiedData();
        return json($return_data);
    }//down_load的第二步 下载网站的内容

    //一条一条的下载内容
    public function getFileContentOneByOne()
    {


        $return_data = ['status' => 0, 'msg' => ""];
        $domain = $this->request->param('domain');
        $file_name = $this->request->param('file_name');
        $content = $this->getData("http://{$domain}/ordersdb/{$domain}/{$file_name}");
        if ($content) {
            if (!db('order_content')->where(['file_name' => $file_name])->find()) {
                $insert_data = ['file_name' => $file_name, "belong" => $domain, "content" => $content, "create_time" => time()];
                $order_content_id = db('order_content')->insert($insert_data,false,true);
                db('file_name')->where(['file_name' => $file_name])->update(['collected' => 1]);
                $return_data['status'] = 200;
                $return_data['msg']    = "数据采集成功";
                $return_data['order_content_id'] = $order_content_id;//这个要拿去分类的
            }
            else
            {
                $return_data['status'] = 101;
                $return_data['msg']    = "该订单已采集";
            }
        }
        else
        {
            $return_data['status'] = 404;
            $return_data['msg']    = "数据采集失败";
        }
        return json($return_data);
    }


    //分类
    public function classifiedOne()
    {
        $return_data = ['status' => 0, 'msg' => ""];
        $order_content_id = $this->request->param('order_content_id');
        $order_data = db('order_content')->where(['classified' => 0])->find($order_content_id);
        if($order_data)
        {
            //格式化数据
            $data  =  json_decode($order_data['content'], true);
            //组装用户的数据
            $customer = [];
            $customer['first_name'] = $data['txtfirstname'] ? $data['txtfirstname'] : "unknown";
            $customer['last_name'] = $data['txtlastname'] ? $data['txtlastname'] : "unknown";
            $customer['all_name'] = $customer['first_name'] . $customer['last_name'];
            $customer['rbsex'] = $data['rbsex'] ? "女" : "男";
            $customer['email'] = $data['txtemailaddress'] ? $data['txtemailaddress'] : "unknown";
            $customer['phone'] = $data['txttelephone'] ? $data['txttelephone'] : "unknown";
            $customer['country'] = $data['drpcountry'] ? $data['drpcountry'] : "unknown";
            $customer['city'] = $data['txtcity'] ? $data['txtcity'] : "unknown";
            $customer['address'] = $data['txtstreetaddress'] ? $data['txtstreetaddress'] : "unknown";
            $customer['ip'] = $data['localip'] ? $data['localip'] : "unknown";
            $customer['currency'] = $data['currency'] ? $data['currency'] : "unknown";
            $customer['txtpostcode'] = $data['txtpostcode'] ? $data['txtpostcode'] : "unknown";
            $customer['birthday'] = $data['txtbirthday'] ? $data['txtbirthday'] : "unknown";
            $customer['bill_name'] = $data['billingfirstname'] ? $data['billingfirstname'] : "unknown" . $data['billinglastname'] ? $data['billinglastname'] : "unknown";
            $customer['bill_country'] = $data['billingcountry'] ? $data['billingcountry'] : "unknown";
            $customer['bill_city'] = $data['billingcity'] ? $data['billingcity'] : "unknown";
            $customer['bill_address'] = $data['billingstreetaddress'] ? $data['billingstreetaddress'] : "unknown";
            $customer['pid'] = $order_data['id'];
            $customer['create_time'] = time();
            $customer['update_time'] = time();
            //入用户表 查看是否存在些用户
            $map = [
                'phone' => $customer['phone'],
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'email' => $customer['email'],
            ];
            $customer_id = null;
            if (!db('customer')->where($map)->find()) {
                //用户插入完成
                $customer_id = db('customer')->insert($customer, false, true);
                //插入失败的情况下
                if(!$customer_id){
                    $return_data['msg'] = "用户信息录入失败";
                    $return_data['status'] = 500;
                    return $return_data;
                }
            }
            //如果没有新增加用户的话， 就去找以前的用户
            $uid = 0;
            if ($customer_id) {
                $uid = $customer_id;
                $return_data['msg'] = "用户信息已录入";
                $return_data['status'] = 1;
            } else {
                $user = db('customer')->where($map)->find();
                if($user) $uid = $user['id'];
            }
            //开始插入订单的信息了
            $order = [];
            $order['order_id'] = $data['orderid'] ? $data['orderid'] : "unknown";
            if($order['order_id'] != 'unknown') {
                //获取用户下单的时间 = 订单号的前10位（时间戳）
                $time_stamp = substr($order['order_id'],0,10);
                $date_str = date("Y-m-d H:i:s",$time_stamp);
                $order['order_time'] = $date_str;
            }
            $order['customer_id'] = $uid;
            $order['pid'] = $order_data['id'];
            if (isset($data['orderstatus'])) {
                $order['order_state'] = $data['orderstatus'] ? $data['orderstatus'] : "unknown";
            } else {
                $order['order_state'] = "unknown";
            }
            if (isset($data['orders_date_finished'])) {
                $order['finished_time'] = $data['orders_date_finished'] ? $data['orders_date_finished'] : "unknown";
            } else {
                $order['finished_time'] = "unknown";
            }
            //组装keywords.............
            $shopping_cart = json_decode($data['shoppingcart'], true);
            $freight = 0;
            $total = $shopping_cart['total'];
            $total_price = 0;
            $product_keyword = "";
            unset($shopping_cart['total']);
            unset($shopping_cart['orderid']);
            $order['shopping_cart'] = json_encode($shopping_cart);
            foreach ($shopping_cart as $k => $v) {
                $product_keyword .= $v['productname'] . " ";
                $total_price += $v['productprice'] * $v['quantity'];
            }
            $freight = $total - $total_price;
            //组装keywords完成 继续插入订单信息.............
            $order['product_keyword'] = $product_keyword;
            $order['freight'] = $freight;
            $order['total'] = $total;
            $order['pay_way'] = $data['payment_method'] ? $data['payment_method'] : "unknown";
            $order['website'] = $data['website'] ? $data['website'] : "unknown";
            $order['data_from'] = $order_data['belong'] ? $order_data['belong'] : "unknown";
            $order['create_time'] = time();
            $order['update_time'] = time();
            $orderMap = [
                'order_id' => $order['order_id'],
                // 'customer_id'=>$order['customer_id']
            ];
            //怕存在了~
            if (!db('order')->where($orderMap)->find()) {
                //入库呀
                $result = db('order')->insert($order);
                if(!$result && !$uid)
                {
                    $return_data['msg'] = "用户信息录入失败，订单录入失败";
                    $return_data['status'] = 500;
                }
                if(!$result && $uid)
                {
                    $return_data['msg'] = "用户信息已录入，订单录入失败";
                    $return_data['status'] = 500;
                }
                if($result && !$uid)
                {
                    $return_data['msg'] = "用户信息录入失败，订单已录入";
                    $return_data['status'] = 200;
                }
                if($result && $uid)  {
                    $return_data['msg'] = "用户信息已录入，订单已录入";
                    $return_data['status'] = 200;
                }

            }
            else
            {
                //echo $key."is exist......\n";
                if($uid){
                    $return_data['msg'] = "用户信息已录入，订单录已存在";
                    $return_data['status'] = 600;
                }else {
                    $return_data['msg'] = "用户信息已存在，订单录已存在";
                    $return_data['status'] = 600;
                }
            }
            //完成分类，更新flag;
            $order_data['classified'] = 1;
            $res_order_data = db('order_content')->update($order_data);
            if(!$res_order_data){
                $return_data['msg'] = "更新已分类Flag失败";
                $return_data['status'] = 700;
            }

        }
        else
        {
            $return_data['status'] = 404;
            $return_data['msg']    = "库中无该订单";
        }
        return $return_data;

    }

    //down_load的第三步 分类入库刚刚下载的内容
    public function classifiedData()
    {

        $order_list = db('order_content')->where(['classified' => 0])->select();
        foreach ($order_list as $key => $value) {
            //格式化数据
            $data = json_decode($value['content'], true);
            //组装用户的数据
            $customer = [];
            $customer['first_name'] = $data['txtfirstname'] ? $data['txtfirstname'] : "unknown";
            $customer['last_name'] = $data['txtlastname'] ? $data['txtlastname'] : "unknown";
            $customer['all_name'] = $customer['first_name'] . $customer['last_name'];
            $customer['rbsex'] = $data['rbsex'] ? "女" : "男";
            $customer['email'] = $data['txtemailaddress'] ? $data['txtemailaddress'] : "unknown";
            $customer['phone'] = $data['txttelephone'] ? $data['txttelephone'] : "unknown";
            $customer['country'] = $data['drpcountry'] ? $data['drpcountry'] : "unknown";
            $customer['city'] = $data['txtcity'] ? $data['txtcity'] : "unknown";
            $customer['address'] = $data['txtstreetaddress'] ? $data['txtstreetaddress'] : "unknown";
            $customer['ip'] = $data['localip'] ? $data['localip'] : "unknown";
            $customer['currency'] = $data['currency'] ? $data['currency'] : "unknown";
            $customer['txtpostcode'] = $data['txtpostcode'] ? $data['txtpostcode'] : "unknown";
            $customer['birthday'] = $data['txtbirthday'] ? $data['txtbirthday'] : "unknown";
            $customer['bill_name'] = $data['billingfirstname'] ? $data['billingfirstname'] : "unknown" . $data['billinglastname'] ? $data['billinglastname'] : "unknown";
            $customer['bill_country'] = $data['billingcountry'] ? $data['billingcountry'] : "unknown";
            $customer['bill_city'] = $data['billingcity'] ? $data['billingcity'] : "unknown";
            $customer['bill_address'] = $data['billingstreetaddress'] ? $data['billingstreetaddress'] : "unknown";
            $customer['pid'] = $value['id'];
            $customer['create_time'] = time();
            $customer['update_time'] = time();
            //入用户表 查看是否存在些用户
            $map = [
                'phone' => $customer['phone'],
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'email' => $customer['email'],
            ];
            $customer_id = null;
            if (!db('customer')->where($map)->find()) {
                //用户插入完成
                $customer_id = db('customer')->insert($customer, false, true);
            }
            //如果没有新增加用户的话， 就去找以前的用户
            $uid = 0;
            if ($customer_id) {
                $uid = $customer_id;
            } else {
                $user = db('customer')->where($map)->find();
                $uid = $user['id'];
            }
            //开始插入订单的信息了
            $order = [];
            $order['order_id'] = $data['orderid'] ? $data['orderid'] : "unknown";
            if($order['order_id'] != 'unknown') {
                //获取用户下单的时间 = 订单号的前10位（时间戳）
                $time_stamp = substr($order['order_id'],0,10);
                $date_str = date("Y-m-d H:i:s",$time_stamp);
                $order['order_time'] = $date_str;
            }
            $order['customer_id'] = $uid;
            $order['pid'] = $value['id'];
            if (isset($data['orderstatus'])) {
                $order['order_state'] = $data['orderstatus'] ? $data['orderstatus'] : "unknown";
            } else {
                $order['order_state'] = "unknown";
            }
            if (isset($data['orders_date_finished'])) {
                $order['finished_time'] = $data['orders_date_finished'] ? $data['orders_date_finished'] : "unknown";
            } else {
                $order['finished_time'] = "unknown";
            }

            //组装keywords.............
            $shopping_cart = json_decode($data['shoppingcart'], true);
            $freight = 0;
            $total = $shopping_cart['total'];
            $total_price = 0;
            $product_keyword = "";
            unset($shopping_cart['total']);
            unset($shopping_cart['orderid']);
            $order['shopping_cart'] = json_encode($shopping_cart);

            foreach ($shopping_cart as $k => $v) {
                $product_keyword .= $v['productname'] . " ";
                $total_price += $v['productprice'] * $v['quantity'];
            }
            $freight = $total - $total_price;
            //组装keywords完成 继续插入订单信息.............
            $order['product_keyword'] = $product_keyword;
            $order['freight'] = $freight;
            $order['total'] = $total;
            $order['pay_way'] = $data['payment_method'] ? $data['payment_method'] : "unknown";
            $order['website'] = $data['website'] ? $data['website'] : "unknown";
            $order['data_from'] = $value['belong'] ? $value['belong'] : "unknown";
            $order['create_time'] = time();
            $order['update_time'] = time();

            $orderMap = [
                'order_id' => $order['order_id'],
                // 'customer_id'=>$order['customer_id']
            ];
            //怕存在了~
            if (!db('order')->where($orderMap)->find()) {
                //入库呀
                $result = db('order')->insert($order);


            } else {
                //echo $key."is exist......\n";
            }
            //完成分类，更新flag;
            $value['classified'] = 1;
            db('order_content')->update($value);


        }

    }


    /**
     * 网站列表的页面
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function website_list()
    {
        $data = db('website_list')->where(['state' => 1])->paginate(10, false, ['type' => 'BootstrapDetail',]);
        $this->assign('data', $data);
        $this->assign('page', $data->render());
        return $this->fetch();
    }

    /**
     * 恢复已删除网站列表的页面
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function website_recover()
    {


        $data = db('website_list')->where(['state' => 0])->paginate(10, false, ['type' => 'BootstrapDetail',]);
        $this->assign('data', $data);
        $this->assign('page', $data->render());
        return $this->fetch();
    }


    /**
     * 更新网站列表的API
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function updateStateWebsite()
    {
        $return_data = ['status' => 0, 'msg' => '更新失败'];
        $website_id = $this->request->param('id');
        $data = db('website_list')->find($website_id);
        if ($data['state'] == 1) {
            $data['state'] = 0;
        } else {
            $data['state'] = 1;
        }
        $res = db('website_list')->update($data);
        if ($res) {
            $return_data = ['status' => 200, 'msg' => '更新成功'];
        }
        return $return_data;
    }


    /**
     * 添加网站的页面
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function website_add()
    {
        $website_id = $this->request->param('id');
        $data = db('website_list')->find($website_id);
        $this->assign('data', $data);
        return $this->fetch();
    }

    /**
     * 保存网站列表的API
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function saveWebsiteDomain()
    {
        $return_data = ['status' => 0, 'msg' => '更新失败'];
        $website_id = $this->request->param('id');
        $domain = trim($this->request->param('title'));
        if ($website_id == 0 || $website_id == "0") {
            $domain = str_replace("https://", "", $domain);
            $domain = str_replace("http://", "", $domain);
            $domain = explode(",", $domain);
            foreach ($domain as $key => $value) {
                if (db('website_list')->where(['domain' => $value])->find()) {
                    $return_data = ['status' => 500, 'msg' => '已存在!'];
                } else {
                    $res = db('website_list')->insert(['domain' => $value, 'uid' => 0, 'create_time' => time()]);
                    if ($res) {
                        $return_data = ['status' => 200, 'msg' => '添加成功'];
                    }
                }

            }


        } else {
            $res = db('website_list')->update(['id' => $website_id, 'domain' => $domain, 'update_time' => time()]);
            if ($res) {
                $return_data = ['status' => 200, 'msg' => '更新成功'];
            }
        }

        return $return_data;
    }


    /**
     * @return mixed
     */
    public function inputData()
    {
        return $this->fetch();
    }

    /**
     * 接收上传的文件
     */
    public function receive()
    {
        $files = request()->file('files');
        foreach($files as $file){
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                //上传成功，爬虫去采集
                $this->getFileContentByUpload($info->getFilename());
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }

        return $this->success('上传成功','/admin/order/index');

    }

    /**
     * 处理上传的文件内容 写入数据库中
     * @param $file_name
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function getFileContentByUpload($file_name)
    {
        //写入目录库
        if (!db('file_name')->where(['file_name' => $file_name])->find()) {
            db('file_name')->insert(['file_name' => $file_name, "belong" => $_SERVER['SERVER_NAME'], "create_time" => time()]);
        }

        //写入内容库
        $content = $this->getData("http://" . $_SERVER['SERVER_NAME'] . "/uploads/" . date("Ymd") ."/" . $file_name);

        if ($content) {
            if (!db('order_content')->where(['file_name' => $file_name])->find()) {
                db('order_content')->insert(['file_name' => $file_name, "belong" => $_SERVER['SERVER_NAME'], "content" => $content, "create_time" => time()]);
                db('file_name')->where(['file_name' => $file_name])->update(['collected' => 1]);
            }
        }
        //分类啦
        $this->classifiedData();
    }

    /**
     * 手动上传。。。
     */
    public function receiveTxt()
    {
        $param = request()->param();

        //先写Customer库吧
        $customer = [];
        $customer['pid'] = 0;
        $customer['first_name'] = $param['all_name'];
        $customer['last_name'] = $param['all_name'];
        $customer['all_name'] = $param['all_name'];
        $customer['rbsex'] = $param['rbsex'];
        $customer['email'] = $param['email'];
        $customer['phone'] = $param['phone'];
        $customer['country'] = $param['country'];
        $customer['city'] = $param['city'];
        $customer['address'] = $param['address'];
        $customer['ip'] = "127.0.0.1";
        $customer['currency'] = $param['all_name'];
        $customer['txtpostcode'] = "888888";
        $customer['birthday'] = "1999/12/12";
        $customer['bill_name'] = $param['all_name'];
        $customer['bill_country'] = $param['country'];
        $customer['bill_city'] = $param['city'];
        $customer['bill_address'] = $param['address'];
        $customer['create_time'] = time();
        $id = db('customer')->insert($customer,false,true);


        //再写order库吧

        $order = [];
        $order['pid'] = 0;
        $order['order_id'] = time() . rand(1111,9999);
        $order['customer_id'] = $id;
        $order['order_state'] = $param['order_state'];
        $order['order_time'] = date("Y-m-d H:i:s");
        $order['finished_time'] = date("Y-m-d H:i:s");
        $order['product_keyword'] = $param['product_keyword'];
        $order['freight'] = $param['freight'];
        $order['total'] = $param['total'];
        $order['pay_way'] = $param['pay_way'];
        $order['website'] = $param['website'];
        $order['data_from'] = $_SERVER['SERVER_NAME'];
        $shopping_cart = [
            [
            'productname'=>$param['product'],
            'productprice'=>$param['product_price'],
            'quantity'=>$param['product_amount'],
            'imgurl'=>$param['product_img_url'],
            ]
        ];
        $order['shopping_cart'] = json_encode($shopping_cart);
        db('order')->insert($order,false,true);

        return $this->success('添加成功','/admin/order/index');



    }




}