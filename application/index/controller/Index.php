<?php

namespace app\index\controller;

use app\admin\model\SystemBanks;
use app\index\model\AppOrder;
use app\index\model\AppPacket;
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








}
