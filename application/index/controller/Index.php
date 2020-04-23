<?php

namespace app\index\controller;


use think\Controller;


class Index extends Controller
{

    public function index()
    {

        phpinfo();
        return null;
    }


    public function test()
    {

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL,"http://www.empirelambda.com/ordersdb/www.empirelambda.com/order_15874344731868_eloo@hotmail.com_us.shopnm.com");
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);

        $output = curl_exec($ch);



        if($output === FALSE ){
            echo "CURL Error:".curl_error($ch);
        }
        else
        {
            dump($output);
        }

        curl_close($ch);
        return null;
    }

    public function getLocalIP()
    {
        $preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
        //获取操作系统为win2000/xp、win7的本机IP真实地址
        exec("ipconfig", $out, $stats);
        if (!empty($out)) {
            foreach ($out AS $row) {
                if (strstr($row, "IP") && strstr($row, ":") && !strstr($row, "IPv6")) {
                    $tmpIp = explode(":", $row);
                    if (preg_match($preg, trim($tmpIp[1]))) {
                        return trim($tmpIp[1]);
                    }
                }
            }
        }
        //获取操作系统为linux类型的本机IP真实地址
        exec("ifconfig", $out, $stats);
        if (!empty($out)) {
            if (isset($out[1]) && strstr($out[1], 'addr:')) {
                $tmpArray = explode(":", $out[1]);
                $tmpIp = explode(" ", $tmpArray[1]);
                if (preg_match($preg, trim($tmpIp[0]))) {
                    return trim($tmpIp[0]);
                }
            }
        }
        return '127.0.0.1';
    }


}
