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
        $AppUser = new AppUser();
        $user = $AppUser->where(['phone'=>$phone])->find();
        if(!$user){
            return json(['msg'=>'不存在该管理员！','status'=>0]);
        }
        //判断密码是否正确
        if(md5($params['password']) != $user['password']){
            return json(['msg'=>'密码错误！','status'=>0]);
        }
        //是否冻结
        if($user['state'] == 0){
            return json(['msg'=>'该用户异常，请联系总管理员！','status'=>0]);
        }
        $data = [
            'last_login_time'=>time(),
            'last_login_ip'=>$this->request->ip(),
        ];
        $AppUser->where(['phone'=>$phone])->update($data);
        session('admin',$user);
        return json(['msg'=>'登录成功！','status'=>200]);
    }
}