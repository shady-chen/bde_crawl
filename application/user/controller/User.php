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

        //判斷邀請碼是否有效
        $invitation_code = $params['invitation_code'];
        $isExist2 = $AppUser->where(['invitation_code'=>$invitation_code])->find();
        if(!$isExist2){
            return json(['msg'=>'邀请码不存在！','status'=>0]);
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
            'update_what'=>'用户自己注册',
            'head_img_url'=>'F:\zz\public\head.png',
        ];

        $data2 = [
            'sons'=>$isExist2['id']+1,
        ];
        $AppUser->save($data);
        $AppUser->where(['id'=>$isExist2['id']])->update($data2);

        return json(['msg'=>'注册成功','status'=>200]);
    }

    /**
     * 修改头像
     */
    public function updateHeadImage(){
        $user = Session::get('user');
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }

        $file = request()->file('img_file');
        if(empty($file))
        {
            return json(['msg'=>'请上传您的头像','status'=>0]);
        }
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH.'public'.DS.'upload'.DS);
        //如果不清楚文件上传的具体键名，可以直接打印$info来查看
        //获取文件（文件名），$info->getFilename()  ***********不同之处，笔记笔记哦
        //获取文件（日期/文件名），$info->getSaveName()  **********不同之处，笔记笔记哦
        $filename = $info->getSaveName();  //在测试的时候也可以直接打印文件名称来查看
        if(!$filename)
        {
            return json(['msg'=>$file->getError(),'status'=>0]);
        }
        //更新头像
        $appUser = new AppUser();
        $appUser->where(['id'=>$user['id']])->update([
            'img_url'=>ROOT_PATH.'public'.DS.'head' . $filename
        ]);
        return json(['msg'=>'更改成功','status'=>200]);
    }

    /**
     * 修改登录密码
     */
    public function updatePassword(){
        $user = Session::get('user');
        $params = $this->request->param();
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }

        //判断验证码
        $code = $params['code'];
        if($code != 8888){
            return json(['msg'=>'验证码错误！','status'=>0]);
        }

        $appUser = new AppUser();
        $appUser->where(['id'=>$user['id']])->update([
            'password'=>md5($params['password']),
        ]);
        return json(['msg'=>'密码修改成功','status'=>200]);
    }

    /**
     * 获取用户session
     */
    public function  getSessionUser(){
        return json(session('user'));
    }
}