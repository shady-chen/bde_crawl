<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/11
 * Time: 11:06
 */

namespace app\user\controller;
use app\index\controller\Index;
use app\index\controller\Smsbao;
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
     * 注册获取验证码
     *
     */
    public function getCode(){
        $smsbao = new Smsbao();
        $params=$this->request->param();
        $phone = $params['phone'];
        $randomNum = rand(999,9999);
        session('registerCode',$randomNum);
        $str = "【投呗】验证码是：".$randomNum . "，五分钟内有效，请勿告诉他人。";

        $result = $smsbao->sendMessage($phone,$str);
        if($result == 0){
            return json(['msg'=>'短信获取成功！','status'=>200]);
        }else{
            return json(['msg'=>'短信获取失败！','status'=>0]);
        }

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
            if($isExist['invitation_code'] == 17040686488 || $isExist['invitation_code'] == '17040686488' ||  $isExist['invitation_code'] == 13123178708 || $isExist['invitation_code'] == '13123178708')
            {
                return json(['msg'=>'推荐号码不可推荐用户注册！','status'=>0]);
            }
            return json(['msg'=>'该手机号已注册！','status'=>0]);
        }




        //判斷邀請碼是否有效
        $invitation_code = $params['invitation_code'];
        $isExist2 = $AppUser->where(['phone'=>$invitation_code])->find();
        if(!$isExist2){
            return json(['msg'=>'邀请码不存在！','status'=>0]);
        }

        //判断验证码
        $code = $params['code'];
        if($code != session('registerCode')){
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
            'head_img_url'=>'\head.png',
        ];

        $data2 = [
            'sons'=>$isExist2['sons']+1,
        ];
        $AppUser->save($data);
        $AppUser->where(['id'=>$isExist2['id']])->update($data2);
        session('registerCode',null);
        return json(['msg'=>'注册成功','status'=>200]);
    }

    /**
     * 修改头像
     */
    public function updateHeadImage(){
        $user = session('user');
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
            'img_url'=>DS.'head' . $filename
        ]);
        return json(['msg'=>'更改成功','status'=>200]);
    }

    /**
     * 修改登录密码
     */
    public function updatePassword(){
        $user = session('user');
        $params = $this->request->param();
        if(!$user){
            return json(['msg'=>'尚未登录！','status'=>0]);
        }

        if($user['password'] != md5($params['oldPass'])){
            return json(['msg'=>'原密码不正确！','status'=>0]);
        }

        $appUser = new AppUser();
        $appUser->where(['id'=>$user['id']])->update([
            'password'=>md5($params['newPass']),
        ]);
        return json(['msg'=>'密码修改成功','status'=>200]);
    }



    /**
     * 修改登录密码2
     */
    public function updatePassword2(){
        $userMondel = new AppUser();
        $params = $this->request->param();
        $user = $userMondel->where(['phone'=>$params['phone']])->find();
        if(!$user){
            return json(['msg'=>'该用户尚未注册','status'=>0]);
        }


        //判断验证码
        $code = $params['code'];
        if($code != session('registerCode')){
            return json(['msg'=>'验证码错误！','status'=>0]);
        }

        $user->where(['id'=>$user['id']])->update([
            'password'=>md5($params['newPass']),
        ]);
        return json(['msg'=>'密码修改成功','status'=>200]);
    }



    /**
     * 获取用户信息
     */
    public function  getUser(){
        $user = session('user');
        $appUser = new AppUser();
        $data = $appUser->where(['id'=>$user['id']])->find();
        return json($data);
    }


    /**
     *
     * session('user')团队
     */
    public function  getUserByInvitationCode(){
        $user = session('user');
        $appUser = new AppUser();
        $slef = $appUser->where(['id'=>$user['id']])->find();

        $data = [];
        $data['self_steam'] = $slef['today_total'];
        $data['self_sons'] = 0;


        $team = $appUser->where(['invitation_code'=>$user['phone']])->select();
        $teamTotal = 0;

        foreach ($team as $key => $val) {

            $teamTotal += $val['today_total'];
            $data['self_sons']++;
        }

        $data['team_total'] = $teamTotal;

        return json($data);
    }


    /**
     *
     * session('user')团队
     */
    public function  getSons(){
        $user = session('user');
        $appUser = new AppUser();



        $team = $appUser->where(['invitation_code'=>$user['phone']])->select();


        return json($team);
    }

    /**
     *
     * 退出
     */
    public function logout(){
        session('user',null);
        return json(['msg'=>'退出','status'=>200]);
    }

    /**
     * 获取用户session
     */
    public function  getSessionUser(){
        return json(session('user'));
    }
}