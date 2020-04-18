<?php


namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;





class Classified extends Command
{
    protected function configure()
    {
        $this->setName('Classified')->setDescription('Here is the mikkle\'s command ');
    }


    protected function execute(Input $input, Output $output)
    {

        echo "start to get data content one by one.......\n";

        $order_list = db('order_content')->where(['classified'=>0])->select();

        $data_count = count($order_list);

        $insert_count = 0;

        echo "Total {$data_count} data.......\n";

        foreach ($order_list as $key=>$value)
        {
            //格式化数据
            $data = json_decode($value['content'],true);

            //组装用户的数据
            $customer = [];
            $customer['first_name'] = $data['txtfirstname']?$data['txtfirstname']:"unknown";
            $customer['last_name'] = $data['txtlastname']?$data['txtlastname']:"unknown";
            $customer['all_name'] = $customer['first_name'].$customer['last_name'];
            $customer['rbsex'] = $data['rbsex']?"女":"男";
            $customer['email'] = $data['txtemailaddress']?$data['txtemailaddress']:"unknown";
            $customer['phone'] = $data['txttelephone']?$data['txttelephone']:"unknown";
            $customer['country'] = $data['drpcountry']?$data['drpcountry']:"unknown";
            $customer['city'] = $data['txtcity']?$data['txtcity']:"unknown";
            $customer['address'] = $data['txtstreetaddress']?$data['txtstreetaddress']:"unknown";
            $customer['ip'] = $data['localip']?$data['localip']:"unknown";
            $customer['currency'] = $data['currency']?$data['currency']:"unknown";
            $customer['txtpostcode'] = $data['txtpostcode']?$data['txtpostcode']:"unknown";
            $customer['birthday'] = $data['txtbirthday']?$data['txtbirthday']:"unknown";
            $customer['bill_name'] = $data['billingfirstname']?$data['billingfirstname']:"unknown" . $data['billinglastname']?$data['billinglastname']:"unknown";
            $customer['bill_country'] = $data['billingcountry']?$data['billingcountry']:"unknown";
            $customer['bill_city'] = $data['billingcity']?$data['billingcity']:"unknown";
            $customer['bill_address'] = $data['billingstreetaddress']?$data['billingstreetaddress']:"unknown";
            $customer['pid'] = $value['id'];
            $customer['create_time'] = time();
            $customer['update_time'] = time();

            //入用户表 查看是否存在些用户
            $map = [
                'phone'=>$customer['phone'],
                'first_name'=>$customer['first_name'],
                'last_name'=>$customer['last_name'],
                'email'=>$customer['email'],
            ];
            $customer_id = null;
            if(!db('customer')->where($map)->find())
            {
                //用户插入完成
                $customer_id = db('customer')->insert($customer,false,true);
                echo "id is ..............".$customer_id."\n";
                echo "insert new guy into the customer named {$customer['first_name']} {$customer['last_name']}..\n";
            }

            //如果没有新增加用户的话， 就去找以前的用户
            $uid = 0;
            if($customer_id)
            {
                $uid = $customer_id;
            }
            else
            {
                $user = db('customer')->where($map)->find();
                $uid = $user['id'];
            }

            if($uid == 0)
            {
                echo "the system cant find the customer Plz check one more";
                exit();
            }

            //开始插入订单的信息了
            $order = [];
            $order['order_id'] = $data['orderid']?$data['orderid']:"unknown";
            $order['customer_id'] = $uid;
            $order['pid'] = $value['id'];
            if(isset($data['orderstatus']))
            {
                $order['order_state'] = $data['orderstatus']?$data['orderstatus']:"unknown";
            }
            else
            {
                $order['order_state'] = "unknown";
            }
            if(isset($data['orders_date_finished']))
            {
                $order['finished_time'] = $data['orders_date_finished']?$data['orders_date_finished']:"unknown";
            }
            else
            {
                $order['finished_time'] = "unknown";
            }


            //组装keywords.............
            $shopping_cart = json_decode($data['shoppingcart'],true);
            $freight = 0;
            $total = $shopping_cart['total'];
            $total_price = 0;
            $product_keyword = "";
            unset($shopping_cart['total']);
            unset($shopping_cart['orderid']);
            $order['shopping_cart'] = json_encode($shopping_cart);
            foreach ($shopping_cart as $k=>$v)
            {
                $product_keyword .= $v['productname']." ";
                $total_price += $v['productprice'] * $v['quantity'];
            }
            $freight = $total - $total_price;
            //组装keywords完成 继续插入订单信息.............
            $order['product_keyword'] = $product_keyword;
            $order['freight'] = $freight;
            $order['total'] = $total;
            $order['pay_way'] = $data['payment_method']?$data['payment_method']:"unknown";
            $order['website'] = $data['website']?$data['website']:"unknown";
            $order['data_from'] = $value['belong']?$value['belong']:"unknown";
            $order['create_time'] = time();
            $order['update_time'] = time();

            $orderMap = [
                'order_id'=>$order['order_id'],
               // 'customer_id'=>$order['customer_id']
            ];
            //怕存在了~
            if(!db('order')->where($orderMap)->find())
            {
                //入库呀
                $result = db('order')->insert($order);
                echo "Insert a new data into the order!......\n";
                echo "......\n";
                echo "......\n";
                echo "......\n";
                echo "......\n";

            }
            else
            {
                echo $key."is exist......\n";
            }
            //完成分类，更新flag;
            $value['classified'] = 1;
            db('order_content')->update($value);
            echo "completed ".sprintf("%.2f",($key+1)/count($order_list)*100)."%......\n";


        }


        echo "....\n";
        echo "....\n";
        echo "....\n";
        echo "....\n";
        echo "O~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~K\n";


    }


}