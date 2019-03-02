<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/18
 * Time: 23:46
 */

namespace app\user\controller;


use app\admin\model\AppMoneysteam;
use app\user\model\AppBanks;
use app\user\model\AppUser;
use app\index\model\AppWithdraw;
use think\Session;
use app\index\model\SystemSetting;
use app\index\controller\Index;




class Withdraw extends Index{
    /**
     * 提现
     */
    public function withdraw(){
        $user = session('user');
        $money_steam = new AppMoneysteam();
        $params = $this->request->param();

        //获得当日0点的时间戳
        $todaytimestemp = strtotime(date("Y-m-d"), time());
        //现在的时间戳
        $now = time();

        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }

//        if($now < $todaytimestemp + (60 * 60 * 9 ) || $now > $todaytimestemp + (60 * 60 * 17 )){
//            return json(['msg'=>'提现时间为早上9点到下午5点，其他时间不予提现！','status'=>1]);
//        }

        if($params['money']%10 != 0){
            return json(['msg'=>'提现只能整十整百','status'=>1]);
        }

        //获取银行卡信息
        $appBanks = new AppBanks();
        $banksData = $appBanks->where(['id'=>$params['bank_id']])->find();

        //用户的金额改变
        $appuser = new AppUser();
        $appData = $appuser->where(['id'=>$user['id']])->find();
        if($params['money']>$appData['money']){
            return json(['msg'=>'余额不足！','status'=>1]);
        }

        $sysSetting = new SystemSetting();
        $sysSettingData = $sysSetting->find();

        $money = $appData['money'] - $params['money'];//提现以后的余额
        if($money+$appData['unclear_money']>=$sysSettingData['full_money']){
            $money2 = $sysSettingData['full_money'];
            $unclear_money = $money+$appData['unclear_money']-$sysSettingData['full_money'];

            //资金明细

            $remark = '金额减少0,未结算金额减少'.$params['money'];

            $money_steam->save([
                'money'=>$params['money'],
                'user_money_now'=>$appData['money'],
                'user_money_later'=>$money2,
                'remark'=>$remark,
                'uid'=>$user['id'],
                'create_time'=>time(),
                'type'=>'提现',
            ]);

        }else{
            $money2 = $money+$appData['unclear_money'];
            $unclear_money = 0;

            //资金明细

            $remark = '金额减少'.($appData['money']-$money2).',未结算金额减少'.$appData['unclear_money'];

            $money_steam->save([
                'money'=>$params['money'],
                'user_money_now'=>$appData['money'],
                'user_money_later'=>$money2,
                'remark'=>$remark,
                'uid'=>$user['id'],
                'create_time'=>time(),
                'type'=>'提现',
            ]);
        }

        $appData2 = [
            'money'=>$money2,
            'unclear_money'=>$unclear_money,
        ];
        $appuser->where(['id'=>$user['id']])->update($appData2);

        $appWithdraw = new AppWithdraw();

        $data = [
            'uid'=>$user['id'],
            'bank_id'=>$params['bank_id'],
            'money'=>$params['money'],
            'states'=>1,
            'remarks'=>$params['remarks'],
            'create_time'=>time(),
            'bank_num'=>$banksData['bank_num'],
            'real_name'=>$banksData['real_name'],
            'bank_which'=>$banksData['bank_which'],
            'bank_where'=>$banksData['bank_where'],
            'user_phone'=>$appData['phone'],

        ];

        $appWithdraw->save($data);
        session('code',null);
        return json(['data'=>$data,'status'=>200]);
    }

    /**
     * 提现记录
     */
    public function withdrawList(){
        $user = session('user');
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }
        $appWithdraw = new AppWithdraw();
        $data = $appWithdraw->where(['uid'=>$user['id']])->order('id desc')->select();

        return json(['data'=>$data,'status'=>200]);
    }

    /**
     * 根据ID查看提现记录
     */
    public function getWithdrawById(){
        $user = session('user');
        $params = $this->request->param();
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }
        $appWithdraw = new AppWithdraw();
        $data = $appWithdraw->where(['id'=>$params['id']])->find();

        return json(['data'=>$data,'status'=>200]);
    }
}