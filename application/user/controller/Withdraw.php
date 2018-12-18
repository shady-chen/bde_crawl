<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/18
 * Time: 23:46
 */

namespace app\user\controller;


use app\user\model\AppBanks;
use app\user\model\AppUser;
use app\index\model\AppWithdraw;
use think\Session;
use app\index\model\SystemSetting;



class Withdraw extends Index{
    /**
     * 提现
     */
    public function withdraw(){
        $user = Session::get('user');
        $params = $this->request->param();
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }

        //获取银行卡信息
        $appBanks = new AppBanks();
        $banksData = $appBanks->where(['id'=>$params['id']])->find();

        //用户的金额改变
        $appuser = new AppUser();
        $appData = $appuser->where(['id'=>$user['id']])->find();
        if($params['money']>$appData['money']){
            return json(['msg'=>'余额不足！','status'=>1]);
        }

        $sysSetting = new SystemSetting();
        $sysSettingData = $sysSetting->find();

        $money = $appData['money'] - $params['money'];
        if($money+$appData['unclear_money']>$sysSettingData['full_money']){
            $money = $sysSettingData['full_money'];
            $unclear_money = $money+$appData['unclear_money']-$sysSettingData['full_money'];

        }else{
            $money = $money+$appData['money'];
            $unclear_money = 0;
        }

        $appData2 = [
            'money'=>$money,
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

        return json(['data'=>$data,'status'=>200]);
    }

    /**
     * 提现记录
     */
    public function withdrawList(){
        $user = Session::get('user');
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }
        $appWithdraw = new AppWithdraw();
        $data = $appWithdraw->where(['uid'=>$user['id']])->selectOrFail();

        return json(['data'=>$data,'status'=>200]);
    }

    /**
     * 根据ID查看提现记录
     */
    public function getWithdrawById(){
        $user = Session::get('user');
        $params = $this->request->param();
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }
        $appWithdraw = new AppWithdraw();
        $data = $appWithdraw->where(['id'=>$params['id']])->find();

        return json(['data'=>$data,'status'=>200]);
    }
}