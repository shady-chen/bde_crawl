<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/13
 * Time: 14:41
 */

namespace app\admin\controller;

use app\user\model\AppUser;

class User extends Base
{
    public  function login(){
        return $this->fetch();
    }

    public function doLogin(){
        $params = $this->request->param();
        $phone = $params['phone'];

        //判断手机号是否已注册

        $user = db('users')->where(['username'=>$phone])->find();
        if(!$user){
            return json(['msg'=>'不存在该管理员！','status'=>0]);
        }
        //判断密码是否正确
        if(md5($params['password']) != $user['password']){
            return json(['msg'=>'密码错误！','status'=>0]);
        }

        session('admin',$user);
        return json(['msg'=>'登录成功！','status'=>200]);
    }


    public function selectUser(){
        $params = $this->request->param();
        $phone = $params['phone'];

        $AppUser = new AppUser();
        $users = $AppUser->where('phone','like',"%".$phone."%")->select();

        return json($users);
    }

}