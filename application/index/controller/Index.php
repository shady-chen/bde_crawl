<?php

namespace app\index\controller;

use app\admin\model\AppNotice;
use app\admin\model\SystemBanks;
use app\index\model\AppOrder;
use app\index\model\AppPacket;
use app\user\model\AppBanks;
use app\user\model\AppUser;
use app\user\model\AppWithdraw;
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
        return json(['data'=>122223]);
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
            return json(['data'=>$data->toArray(),'status'=>200]);
        }

        return json(['msg'=>'还没有包发出来','status'=>0]);

    }


    public function robPacket()
    {

        $user = Session::get('user');

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
            return json(['msg'=>'该红包已抢完！','status'=>0]);
        }

        //是否已抢过这个包
        $isAlreadyRob = $appOrder->where(['packet_id'=>$data['id']])->find();
        if($isAlreadyRob) return json(['msg'=>'您已抢过该红包！','status'=>0]);


        //修改红包数量
        $appPacket->where(['id'=>$data['id']])->update(['amount'=> ((int)$data['amount'])-1]);



        $SystemSetting = new SystemSetting();
        $setting = $SystemSetting->where('id', 1)->find();

        $SystemBanks = new SystemBanks();
        $banks = $SystemBanks->where(['is_use'=>1])->select();

        $coun = count($banks);
        $ran = rand(0,$coun-1);

        //存入order表中
        $appOrder->save([
            'uid'=>$user['id'],
            'packet_id'=>$data['id'],
            'user_phone'=>$user['phone'],
            'packet_expect'=>$data['expect'],
            'money'=>round($setting['per_total']/$setting['how_many']),
            'status'=>1,
            'img_url'=>'',
            'remarks'=>'',
            'create_time'=>time(),
            'sys_bank_num'=>$banks[$ran]['bank_num'],
            'sys_bank_which'=>$banks[$ran]['bank_which'],
            'sys_bank_where'=>$banks[$ran]['bank_where'],
            'sys_name'=>$banks[$ran]['name'],
        ]);

        return json(['msg'=>'您已成功抢到'. $data['expect'] .'期的红包,订单号为'.$data['id'],'status'=>200,'amount'=>((int)$data['amount'])-1]);

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
        $data2 = $appBanks->where(['id'=>$params['id']])->find();

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
            'bank_num'=>$data2['bank_num'],
            'real_name'=>$data2['real_name'],
            'bank_which'=>$data2['bank_which'],
            'bank_where'=>$data2['bank_where'],

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
     * 添加银行卡
     */
    public function addAppBanks(){
        $user = Session::get('user');
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
        ];

        $appBanks = new AppBanks();

        $appBanks->save($data);
        return json(['data'=>$data,'status'=>200]);
    }
    /**
     * 银行卡列表
     */
    public function appBanksList(){
        $user = Session::get('user');
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }
        $appBanks = new AppBanks();
        $data = $appBanks->where(['uid'=>$user['id']])->selectOrFail();

        return json(['data'=>$data,'status'=>200]);
    }
    /**
     * 根据ID获取银行卡
     */
    public function getBankById(){
        $user = Session::get('user');
        $params = $this->request->param();
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }
        $appBanks = new AppBanks();
        $data = $appBanks->where(['id'=>$params['id']])->find();

        return json(['data'=>$data,'status'=>200]);
    }
    /**
     * 获取通知列表
     */
    public function getNoticeByUid(){
        $user = Session::get('user');
        $params = $this->request->param();
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }
        $appNotice = new AppNotice();

        $data = $appNotice->where(['uid'=>$user['id']])->selectOrFail();

        return json(['data'=>$data,'status'=>200]);
    }






}
