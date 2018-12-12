<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/11
 * Time: 11:06
 */

namespace app\user\controller;
use app\index\controller\Index;
use app\user\model\AppUser;

class User extends Index
{
    public function index(){
        return $this->fetch();
    }

    /**
     * 用户登录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login(){
        $params = $this->request->param();

        $phone = $params['phone'];
        if(strlen($phone) != 11){
            return json(['msg'=>'手机号有误！','status'=>0]);
        }
        //判断手机号是否已注册
        $AppUser = new AppUser();
        $user = $AppUser->where(['phone'=>$phone])->find();
        if(!$user){
            return json(['msg'=>'该手机号尚未注册！','status'=>0]);
        }
        //判断密码是否正确
        if(md5($params['password']) != $user['password']){
            return json(['msg'=>'密码错误！','status'=>0]);
        }
        //是否冻结
        if($user['state'] == 0){
            return json(['msg'=>'该用户异常，请联系管理员！','status'=>0]);
        }


        $data = [
            'last_login_time'=>time(),
            'last_login_ip'=>$this->request->ip(),
        ];
        $AppUser->where(['phone'=>$phone])->update($data);
        session('user',$user);
        return json(['msg'=>'登录成功！','status'=>200]);


    }

    /**
     * 用户注册
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function register(){

        $params = $this->request->param();
        //return $params;
        $phone =  $params['phone'];

        //判断手机号是否合格
        if(strlen($phone) != 11){
            return json(['msg'=>'手机号码格式不正确','status'=>0]);
        }

        //判断手机号是否已注册
        $AppUser = new AppUser();
        $isExist = $AppUser->where(['phone'=>$phone])->find();
        if($isExist){
            return json(['msg'=>'该手机号已注册！','status'=>0]);
        }
        //判断验证码
        $code = $params['code'];
        if($code != 8888){
            return json(['msg'=>'验证码错误！','status'=>0]);
        }

        $data = [
            'phone'=>$phone,
            'password'=>md5($params['password']),
            'money'=>0,
            'unclear_money'=>0,
            'type'=>1,
            'state'=>1,
            'invitation_code'=>$params['invitation_code']?$params['invitation_code']:0,
            'today_total'=>0,
            'sons'=>0,
            'last_login_time'=>time(),
            'last_login_ip'=>$this->request->ip(),
            'bonus'=>0,
            'create_time'=>time(),
            'update_time'=>time(),
            'update_what'=>'用户自己注册'
        ];
        $AppUser->save($data);

        return json(['msg'=>'注册成功','status'=>200]);
    }
}