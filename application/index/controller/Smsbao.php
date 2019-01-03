<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 13:53
 */
namespace app\index\controller;

use app\index\controller\Index;

class Smsbao extends Index {
    /**发送单条信息
     * @param $phone
     * @param $content
     * @return false|string
     */
    public function sendMessage($phone,$content){
        $smsapi = "http://api.smsbao.com/";
        $user = "15880630261"; //短信平台帐号
        $pass = md5("sw950825"); //短信平台密码
//        $content="短信内容";//要发送的短信内容
//        $phone = "*****";//要发送短信的手机号码
        $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
        $result =file_get_contents($sendurl) ;
        return $result;
    }

    /**
     * 发送多条信息
     * @param $phone
     * @param $content
     * @return false|string
     */
    public function sendMessages($phone,$content){
        $smsapi = "http://api.smsbao.com/";
        $user = "15880630261"; //短信平台帐号
        $pass = md5("sw950825"); //短信平台密码
//        $content="短信内容";//要发送的短信内容
//        $phone = "*****";//要发送短信的手机号码
        $phones = $phone[0];
        for($i = 1;$i<sizeof($phone);$i++){
            $phones .= ','.$phone[$i];
        }

        $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phones."&c=".urlencode($content);
        $result =file_get_contents($sendurl) ;
        return $result;
    }

    /**
     * 获取验证码
     * @return \think\response\Json
     */
    public function getCode(){
        $params=$this->request->param();
        $phone = $params['phone'];
        $randomNum = rand(999,9999);
        session('code',$randomNum);
        $str = "验证码是：".$randomNum;

        $result = $this->sendMessage($phone,$str);

        if($result == 0){
            return json(['msg'=>'短信获取成功！','status'=>200]);
        }else{
            return json(['msg'=>'短信获取失败！','status'=>0]);
        }
    }
}