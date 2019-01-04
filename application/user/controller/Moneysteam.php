<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/30
 * Time: 16:26
 */

namespace app\user\controller;

use app\admin\model\AppMoneysteam;
use app\index\controller\Index;

class Moneysteam extends Index{
    /**
 * 获取资金明细列表
 */
    public function getMoneysteamByUid(){
        $user = session('user');
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }
        $appMoneysteam = new AppMoneysteam();

        $data = $appMoneysteam->where('uid',$user['id'])->select();

        return json(['data'=>$data,'status'=>200]);
    }

    /**
     * 获取资金明细
     */
    public function getMoneysteamById(){
        $user = session('user');
        $params = $this->request->param();
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }
        $appMoneysteam = new AppMoneysteam();

        $data = $appMoneysteam->where('id',$params['id'])->find();

        return json(['data'=>$data,'status'=>200]);
    }
}