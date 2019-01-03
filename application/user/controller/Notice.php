<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/18
 * Time: 23:58
 */

namespace app\user\controller;


use app\index\controller\Index;
use app\admin\model\AppNotice;;
use think\Session;



class Notice extends Index{
    /**
     * 获取通知列表
     */
    public function getNoticeByUid(){
        $user = session('user');
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }
        $appNotice = new AppNotice();

        $data = $appNotice->where(function ($query) USE ($user){
            $query->where('uid',$user['id'])->whereOr('uid',0);
        })->where(['states'=>1])->select();

        return json(['data'=>$data,'status'=>200]);
    }

    /**
     * 查看单个通知列表
     */
    public function getNoticeById(){
        $user = session('user');
        $params = $this->request->param();
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }
        $appNotice = new AppNotice();

        $data = $appNotice->where(['id'=>$params['id']])->find();

        return json(['data'=>$data,'status'=>200]);
    }

}