<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/18
 * Time: 23:56
 */

namespace app\user\controller;


use app\index\controller\Index;
use app\user\model\AppBanks;
use app\index\model\AppWithdraw;
use think\Session;



class Bank extends Index{
    /**
     * 添加银行卡
     */
    public function addAppBanks(){
        $user = session('user');
        $params = $this->request->param();
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }

        $data = [
            'uid'=>$user['id'],
            'bank_num'=>$params['bank_num'],
            'real_name'=>$params['real_name'],
            'bank_which'=>$params['bank_which'],
            'bank_where'=>$params['bank_where'],
            'create_time'=>time(),
            'status'=>1,
        ];

        $appBanks = new AppBanks();

        $appBanks->save($data);
        return json(['data'=>$data,'status'=>200]);
    }

    /**
     * 银行卡列表
     */
    public function appBanksList(){
        $user = session('user');
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }
        $appBanks = new AppBanks();
        $data = $appBanks->where(['uid'=>$user['id']])->where(['status'=>1])->order('create_time desc')->select();

        return json(['data'=>$data,'status'=>200]);
    }

    /**
     * 修改银行卡
     */
    public function updateAppBank(){
        $user = session('user');
        $params = $this->request->param();
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }

        $appWithdraw = new AppWithdraw();
        $appWithdrawData = $appWithdraw->where(['bank_id'=>$params['id']])->where(['states'=>1])->find();
        if($appWithdrawData != null ){
            return json(['msg'=>'您还有提现订单未审核！如需修改银行卡信息,请联系管理员!','status'=>0]);
        }

        $appBanks = new AppBanks();
        $data = [
            'bank_num'=>$params['bank_num'],
            'real_name'=>$params['real_name'],
            'bank_which'=>$params['bank_which'],
            'bank_where'=>$params['bank_where'],
            'create_time'=>time(),
        ];

        $appBanks->where(['id'=>$params['id']])->update($data);
        return json(['msg'=>'修改成功！','status'=>200]);
    }

    /**
     * 删除银行卡
     */
    public function deleteAppBank(){
        $user = session('user');
        $params = $this->request->param();
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }
        $appBanks = new AppBanks();
        $data = [
            'status'=>0
        ];
        $appBanks->where(['id'=>$params['id']])->update($data);

        return json(['msg'=>'删除成功！','status'=>200]);

    }

    /**
     * 根据ID获取银行卡
     */
    public function getAppBankById(){
        $user = session('user');
        $params = $this->request->param();
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }
        $appBanks = new AppBanks();
        $data = $appBanks->where(['id'=>$params['id']])->find();

        return json(['data'=>$data,'status'=>200]);
    }
}
