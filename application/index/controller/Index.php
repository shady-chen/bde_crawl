<?php

namespace app\index\controller;

use app\admin\model\AppMoneysteam;
use app\admin\model\SystemBanks;
use app\index\model\AppOrder;
use app\index\model\AppPacket;
use app\user\model\AppUser;
use think\Controller;
use think\Session;
use app\index\model\SystemSetting;


/**
 * Class Index
 * @package app\index\controller
 * 这个类作为基础类
 */


class Index extends Controller
{

    public function index()
    {
        return $this->fetch();
    }


    /**
     * 获取最新的包
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isExistPacket()
    {
        $appPacket = new AppPacket();

        $SystemSetting = new SystemSetting();
        $setting = $SystemSetting->where('id', 1)->find();

        $data = $appPacket->where('create_time','>',time()-$setting['how_long'])->find();

        if($data){
            return json(['data'=>$data->toArray(),'status'=>200,'setting'=>$setting]);
        }

        return json(['msg'=>'还没有包发出来','status'=>0,'setting'=>$setting]);

    }

    /**
     * 发放奖励
     * @return \think\response\Json
     *
     */
    public function award(){
        $setting = new SystemSetting();
        $settingData = $setting->find();
        $money_steam = new AppMoneysteam();

        $user = new AppUser();
        $userData = $user->where(['type'=>1])->select();

        for ($x = 0; $x<count($userData);$x++)
        {
            $userXiaji = $user->where(['type'=>1])->where(['invitation_code'=>$userData[$x]['phone']])->select();
            $totalSum = $userData[$x]['today_total'];
            if(!empty($userXiaji)){
                for ($y = 0; $y<count($userXiaji);$y++){
                    $totalSum += $userXiaji[$y]['today_total'];
                }
            }

            //发放星级奖励
            $addMoneyXing = 0;
            if($totalSum >= 2000000 && $totalSum <5000000){
                $addMoneyXing = 2000;


            }else if($totalSum >= 5000000 && $totalSum <8000000){
                $addMoneyXing = 5000;
            }else if($totalSum >= 8000000){
                $addMoneyXing = 8000;
            }
            if($addMoneyXing != 0){
                $this->awardXing($userData[$x],$addMoneyXing,'发放星级奖励');
            }
            //发放推荐人团队奖励
            if($userData[$x]['sons']+1>=$settingData['sons'] && $totalSum>=$settingData['bonus_rule'])
            {

                $addMoney = floor($totalSum/$settingData['bonus_rule']) * $settingData['per_money'];

                if($userData[$x]['money']+$addMoney>$settingData['full_money'])
                {
                    $money = $settingData['full_money'];
                    $unclear_money = $userData[$x]['money']+$userData[$x]['unclear_money']+$addMoney-$settingData['full_money'];
                    $user->where(['id'=>$userData[$x]['id']])->update([
                        'money'=>$money,
                        'unclear_money'=>$unclear_money,
                    ]);


                    //资金明细
                    $remark = '金额增加'.($settingData['full_money']-$userData[$x]['money']).',未结算金额增加'.($unclear_money-$userData[$x]['unclear_money']);

                    $money_steam->save([
                        'money'=>$addMoney,
                        'user_money_now'=>$userData[$x]['money'],
                        'user_money_later'=>$money,
                        'remark'=>$remark,
                        'uid'=>$userData[$x]['id'],
                        'create_time'=>time(),
                        'type'=>'发放奖励',
                    ]);



                }
                else
                {
                    $money = $userData[$x]['money']+ $addMoney ;
                    $user->where(['id'=>$userData[$x]['id']])->update([
                        'money'=>$money,
                    ]);


                    //资金明细

                    $remark = '金额增加'.$settingData['per_money'].',未结算金额增加0';

                    $money_steam->save([
                        'money'=>$settingData['per_money'],
                        'user_money_now'=>$userData[$x]['money'],
                        'user_money_later'=>$money,
                        'remark'=>$remark,
                        'uid'=>$userData[$x]['id'],
                        'create_time'=>time(),
                        'type'=>'发放奖励',
                    ]);

                }
                echo $userData[$x]['phone'].'的奖励发送成功！';
            }





        }

        //清除今天的打码量
        for ($x = 0; $x<count($userData);$x++){
            $user->where(['id'=>$userData[$x]['id']])->update([
                'today_total'=>0,
            ]);
        }


    }


    /**
     *
     * 星级奖励调用的方法
     */
    public function awardXing($user,$addMoney,$content){
        $setting = new SystemSetting();
        $settingData = $setting->find();
        $money_steam = new AppMoneysteam();

        if($user['money']+$addMoney>$settingData['full_money'])
        {
            $money = $settingData['full_money'];
            $unclear_money = $user['money']+$user['unclear_money']+$addMoney-$settingData['full_money'];
            $user->where(['id'=>$user['id']])->update([
                'money'=>$money,
                'unclear_money'=>$unclear_money,
            ]);


            //资金明细
            $remark = '金额增加'.($settingData['full_money']-$user['money']).',未结算金额增加'.($unclear_money-$user['unclear_money']);

            $money_steam->save([
                'money'=>$addMoney,
                'user_money_now'=>$user['money'],
                'user_money_later'=>$money,
                'remark'=>$remark,
                'uid'=>$user['id'],
                'create_time'=>time(),
                'type'=>$content,
            ]);



        }
        else
        {
            $money = $user['money']+ $addMoney ;
            $user->where(['id'=>$user['id']])->update([
                'money'=>$money,
            ]);


            //资金明细

            $remark = '金额增加'.$settingData['per_money'].',未结算金额增加0';

            $money_steam->save([
                'money'=>$settingData['per_money'],
                'user_money_now'=>$user['money'],
                'user_money_later'=>$money,
                'remark'=>$remark,
                'uid'=>$user['id'],
                'create_time'=>time(),
                'type'=>$content,
            ]);

        }
    }


    /**
     * 抢包
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function robPacket()
    {

        $user = session('user');

        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }
        if($user['state'] == 2){
            return json(['msg'=>'您暂时不能抢红包，请联系管理员！','status'=>0]);
        }

        $appOrder = new AppOrder();
        //期数
        $expect = $this->request->param('expect');
        //user



        //找到红包
        $appPacket = new AppPacket();
        $data = $appPacket->where(['expect'=>$expect])->find();

        //是否还有包
        if(!$data ) return json(['msg'=>'期数异常！','status'=>0]);

        if($data['amount'] == 0){
            return json(['msg'=>'该任务已被领取完！','status'=>0]);
        }

        //是否已抢过这个包
        $isAlreadyRob = $appOrder->where(['packet_id'=>$data['id']])->where(['uid'=>$user['id']])->find();
        if($isAlreadyRob) return json(['msg'=>'您已领取过该任务！','status'=>0]);


        //修改红包数量
        $appPacket->where(['id'=>$data['id']])->update(['amount'=> ((int)$data['amount'])-1]);



        $SystemSetting = new SystemSetting();
        $setting = $SystemSetting->where('id', 1)->find();

        $SystemBanks = new SystemBanks();
        $banks = $SystemBanks->where(['is_use'=>1])->select();

        //获取随机银行卡
        $coun = count($banks);
        $ran = rand(0,$coun-1);
        //获取随机金额
//        $orderData = $appOrder->where(['packet_id'=>$data['id']])->select();
//        $shengxiaMoney = $data['money'];
//        $shengxiageshu = ((int)$data['amount']);

//        if($shengxiageshu == 1){
//            $randMoney = $shengxiaMoney;
//        }else{
//            if($orderData != null){//如果有订单就要获取剩下的金额再随机
//
//                for($x = 0;$x<count($orderData);$x++){
//                    $shengxiaMoney -= $orderData[$x][money];
//                }
//                //每次发包的平均金额必须大于系统设置在最小金额
//            }
//            $randMoney = rand($setting['minManey'],$setting['maxManey']);
//        }
        //2-8 先确保能用的 302行
        $randMoney = rand($setting['minManey'],$setting['maxManey']);

        //存入order表中
        $appOrder->save([
            'uid'=>$user['id'],
            'packet_id'=>$data['id'],
            'user_phone'=>$user['phone'],
            'packet_expect'=>$data['expect'],
            'money'=>$randMoney,
            'status'=>1,
            'img_url'=>'',
            'remarks'=>'',
            'create_time'=>time(),
            'sys_bank_num'=>$banks[$ran]['bank_num'],
            'sys_bank_which'=>$banks[$ran]['bank_which'],
            'sys_bank_where'=>$banks[$ran]['bank_where'],
            'sys_name'=>$banks[$ran]['name'],
        ]);


        /*$smsbao = new Smsbao();
        $phone = $user['phone'];
        $str = "您已经抢到红包，请在15分钟之内付款";

        $smsbao->sendMessage($phone,$str);*/
//        if($result == 0){
//            return json(['msg'=>'短信获取成功！','status'=>200]);
//        }else{
//            return json(['msg'=>'短信获取失败！','status'=>0]);
//        }

        return json(['msg'=>'您已成功领取到'. $data['expect'] .'期的任务,订单号为'.$data['id'],'status'=>200,'amount'=>((int)$data['amount'])-1,'money'=>$randMoney]);

    }

    /**
     * 根据ID找红包
     */
    public function findPackById(){
        $id = $this->request->param("id");
        $appOrder = new AppOrder();
        $data = $appOrder->where(['id'=>$id])->find();
        return json(['data'=>$data,'status'=>200]);
    }



    /**
     * 让订单过期
     *
     */
    public function updateOrder()
    {
        $appOrder = new AppOrder();
        $appOrderData = $appOrder->where(['status' => 1])->select();

        //获得当日0点的时间戳
        $todaytimestemp = strtotime(date("Y-m-d"), time());

        $now = time();


        for ($x = 0; $x < count($appOrderData); $x++) {

            if ($appOrderData[$x]['create_time'] >= ($todaytimestemp + 60 * 60 * 8) && $appOrderData[$x]['create_time'] <= ($todaytimestemp + 60 * 60 * 17)) {
                if ($now - $appOrderData[$x]['create_time'] >= 2 * 60 * 60) {
                    $appOrder->where(['id' => $appOrderData[$x]['id']])->update([
                        'status' => 0
                    ]);
                }
            } else {
                if ($now == ($todaytimestemp + 60 * 60 * 10)) {
                    $appOrder->where(['id' => $appOrderData[$x]['id']])->update([
                        'status' => 0
                    ]);
                }


            }
        }
    }


    /**
     * 获取游戏规则内容
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSettingData(){

        $setting = new SystemSetting();
        $data = $setting->where(['id'=>1])->find();

        return json($data);
    }




}
