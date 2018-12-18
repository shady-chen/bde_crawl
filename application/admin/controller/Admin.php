<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 21:23
 */

namespace app\admin\controller;




use app\admin\model\AppNotice;
use app\admin\model\Notice;
use app\index\model\AppOrder;
use app\admin\model\SystemBanks;
use app\index\model\SystemSetting;
use app\user\model\AppBanks;
use app\user\model\AppUser;
use app\index\model\AppWithdraw;


class Admin extends Base
{
    function _initialize()
    {
        if(!$this->isAdmin()){
            return $this->error('您还没有登录！','/admin/user/login');
        }
        //parent::_initialize(); // TODO: Change the autogenerated stub
    }

    public function index(){
        return $this->fetch();
    }

    /**
     * 通知列表
     * @return mixed
     */
    public function article_list(){
        $notice = new AppNotice();
        $title = $this->request->param('title');
        if($title != null && $title != ''){
            $data = $notice->where('title','like','%'.$title.'%')->order('create_time desc')->paginate(10);
        }else{
            $data = $notice->order('create_time desc')->paginate(10);
        }



        $this->assign('data',$data);
        $this->assign('page',$data->render());
        return $this->fetch();
    }
    /**
     * 发布和下架通知
     */
    public function updateStateNotice(){
        $notice = new AppNotice();
        $params = $this->request->param();
        $data = [
            'states'=>$params['states'],
        ];
        $notice->where(['id'=>$params['id']])->update($data);
        return json(['status'=>200]);
    }

    /**
     * 删除通知
     */
    public function deleteNotice(){
        $id = $this->request->param('id');
        $notice = new AppNotice();
        $notice->where(['id'=>$id])->delete();
        return json(['status'=>200]);
    }


    /**
     * 系统发送通知
     * @return mixed
     */
    public function article_add($id){
        $notice = new AppNotice();
        if($id == 0){
            $data = null;
        }else{
            $data = $notice->where(['id'=>$id])->find();
        }
        $this->assign('data',$data);

        return $this->fetch();
    }

    /**
     * 添加通知
     */
    public function saveNotice(){
        $notice = new AppNotice();
        $params = $this->request->param();
        $data = [
            'uid'=>$params['uid'],
            'title'=>$params['title'],
            'content'=>$params['content'],
            'states'=>0,
            'create_time'=>time(),
        ];
        if($params['id'] == null || $params['id'] == ''){
            $notice->save($data);
            $msg = '添加成功';
        }else{
            $notice->where(['id'=>$params['id']])->update($data);
            $msg =  '更新成功';
        }

        return json(['status'=>200,'msg'=>$msg]);
    }


    /**
     * 订单列表
     * @return mixed
     */
    public function order_list(){
        $appOrder = new AppOrder();
        $phone = $this->request->param('phone');
        if($phone != null && $phone != ''){
            $data = $appOrder->where('user_phone','like','%'.$phone.'%')->order('create_time desc')->paginate(10);

        }else{
            $data = $appOrder->order('create_time desc')->paginate(10);
        }
        $this->assign('data',$data);
        $this->assign('page',$data->render());
        return $this->fetch();
    }

    /**
     * 银行卡列表
     * @return mixed
     */
    public function banks_list(){


        $SystemBanks = new SystemBanks();
        $bankName = $this->request->param('banksName');
        if($bankName != null && $bankName != ''){
            $data = $SystemBanks->where(['is_use'=>1])->where('bank_which','like','%'.$bankName.'%')->order('create_time desc')->paginate(10);

        }else{
            $data = $SystemBanks->where(['is_use'=>1])->order('create_time desc')->paginate(10);
        }
        $this->assign('data',$data);
        $this->assign('page',$data->render());
        return $this->fetch();
    }

    /**
     * 删除银行卡
     */
    public function deleteBank(){
        $id = $this->request->param('id');
        $systemBanks = new SystemBanks();

        $data = [
            'is_use'=>0,
        ];

        $systemBanks->where(['id'=>$id])->update($data);
        return json(['msg'=>'刪除成功','status'=>200]);
    }
    /**
     * 系统添加银行卡
     * @return mixed
     */
    public function banks_add(){
        return $this->fetch();
    }

    /**
     * 系统添加银行卡
     * @return json
     */
    public function doAddBanks(){
        $params = $this->request->param();

        $SystemBanks = new SystemBanks();
        $data = [
            'bank_num'=>$params['bank_num'],
            'bank_which'=>$params['bank_which'],
            'bank_where'=>$params['bank_where'],
            'name'=>$params['name'],
            'is_use'=>1,
            'create_time'=>time(),
        ];
        $SystemBanks->save($data);

        return json(['msg'=>'添加成功','status'=>200]);

    }
    /**
     * 跳转系统设置页面
     */
    public function system_base(){
        $sys_setting = new SystemSetting();
        $data = $sys_setting->find();
        $this->assign('data',$data);
        return $this->fetch();

    }
    /**
     * 系统设置的修改
     */
    public function system_base_update(){
        $params = $this->request->param();
        $sys_setting = new SystemSetting();
        if($params['bonus_rule']<=0){
            return json(['msg'=>'阶级金额不能小于0','status'=>0]);
        }
        if($params['star_time']<0 || $params['star_time']>=$params['end_time'] || $params['end_time']>23){
            return json(['msg'=>'开始时间不能大于结束时间','status'=>0]);
        }
        if($params['per_total']<=0){
            return json(['msg'=>'发包总金额不能小于0','status'=>0]);
        }
        if($params['how_many']<=0){
            return json(['msg'=>'发包不能为小于1','status'=>0]);
        }
        if($params['how_long']<=0){
            return json(['msg'=>'发包间隔时间不能小于0','status'=>0]);
        }
        if($params['full_money']<0){
            return json(['msg'=>'满多少金额的值不能小于0','status'=>0]);
        }
        if($params['sons']<0){
            return json(['msg'=>'下线不能小于0','status'=>0]);
        }
        $data = [
            'bonus_rule'=>$params['bonus_rule'],
            'per_money'=>$params['per_money'],
            'star_time'=>$params['star_time'],
            'per_total'=>$params['per_total'],
            'how_many'=>$params['how_many'],
            'end_time'=>$params['end_time'],
            'how_long'=>$params['how_long'],
            'bunus_money'=>$params['bunus_money'],
            'full_money'=>$params['full_money'],
            'sons'=>$params['sons'],
        ];

        $sys_setting->where(['id'=>1])->update($data);
        return json(['msg'=>'修改成功','status'=>200]);
    }
    /**
     * 會員列表
     */
    public function user_list(){


        $appUser = new AppUser();
        $phone = $this->request->param('phone');
        if($phone != null && $phone != ''){
            $data = $appUser->where('phone','like','%'.$phone.'%')->order('create_time desc')->paginate(10);

        }else{
            $data = $appUser->where(['type'=>1])->order('create_time desc')->paginate(10);
        }
        $this->assign('data',$data);
        $this->assign('page',$data->render());
        return $this->fetch();
    }

    /**
     * 查看用户银行
     */
    public function selectUserBanks(){
        $id = $this->request->param('id');
        $appBanks = new AppBanks();
        $data = $appBanks->where('uid','=',$id)->where('status','=',1)->selectOrFail();

        if($data == null){
            return json(['msg'=>用户还没添加银行卡,'status'=>0]);
        }

        return json(['data'=>$data,'status'=>200]);
    }




    /**
     * 用戶修改
     */
    public function updateUser(){
        $id = $this->request->param('id');
        $appUser = new AppUser();
        $data = $appUser->where(['id'=>$id])->find();

        return json(['data'=>$data,'status'=>200]);
    }



    /**
     * 保存修改用户
     */
    public function saveUpdateUser(){
        $param = $this->request->param();
        $appUser = new AppUser();
        $id=$param['id'];
        $user = $appUser->where(['id'=>$id])->find();

        $str = '';

        if($user['money'] != $param['money']){
            $str .='修改用户金额.  ';
        }
        if($user['unclear_money'] != $param['unclear_money']){
            $str .='修改冻结金额.  ';
        }
        if($user['bonus'] != $param['bonus']){
            $str .='修改总奖金金额.  ';
        }
        if($user['today_total'] != $param['today_total']){
            $str .='修改今天打码数.  ';
        }
        if($user['state'] != $param['state']){
            $str .='修改用户状态.  ';
        }
        $data = [
            'money'=>$param['money'],
            'unclear_money'=>$param['unclear_money'],
            'bonus'=>$param['bonus'],
            'today_total'=>$param['today_total'],
            'state'=>$param['state'],
            'update_time'=>time(),
            'update_what'=>$str,
        ];
        $appUser->where(['id'=>$id])->update($data);
        return json(['msg'=>'修改成功','status'=>200]);
    }
    /**
     * 用戶刪除
     */
    public function  deleteUser(){
        $id = $this->request->param('id');
        $appUser = new AppUser();

        $data = [
            'state'=>0,
        ];

        $appUser->where(['id'=>$id])->update($data);
        return json(['msg'=>'刪除成功','status'=>200]);
    }

    /**
     * 根据用户名查找用户
     */
    public function  findUser(){
        $phone = $this->request->param('phone');
        $appUser = new AppUser();
        $data = $appUser->where(['type'=>1])->where('phone','like','%'.$phone.'%')->order('create_time desc')->paginate(10);

        return json(['data'=>$data,'status'=>200]);
    }

    /**
     * 提现列表
     */
    public function withdraw_list(){
        $appWithdraw = new AppWithdraw();
        $phone = $this->request->param('phone');
        if($phone != null && $phone != ''){
            $data = $appWithdraw->where('user_phone','like','%'.$phone.'%')->order('create_time desc')->paginate(10);

        }else{
            $data = $appWithdraw->order('create_time desc')->paginate(10);
        }
        $this->assign('data',$data);
        $this->assign('page',$data->render());
        return $this->fetch();
    }

    /**
     * 提现审核弹框
     */
    public function checkWithdraw(){
        $id = $this->request->param('id');
        $appWithdraw = new AppWithdraw();
        $data = $appWithdraw->where(['id'=>$id])->find();

        return json(['data'=>$data,'status'=>200]);
    }

    /**
     * 提现审核
     */
    public function passWithdraw(){
        $params = $this->request->param();
        $status = $params['states'];

        $appWithdraw = new AppWithdraw();
        if($status == 2){
            $data = [
                'states'=>$params['states'],
            ];
            $appWithdraw->where(['id'=>$params['id']])->update($data);
            return json(['msg'=>'审核通过','status'=>200]);
        }
        if($status == 3){
            $data = [
                'states'=>$params['states'],
            ];

            $appUser = new AppUser();
            $appUserData = $appUser->where(['phone'=>$params['user_phone']])->find();

            $appWithdrawData = $appWithdraw->where(['id'=>$params['id']])->find();

            $sysSetting = new SystemSetting();
            $sysSettingData = $sysSetting->find();

            if($appUserData['money']+$appWithdrawData['money']>$sysSettingData['full_money']){
                $lostMoney = $appUserData['money']+$appWithdrawData['money']-$sysSettingData['full_money'];
                $appUserData['unclear_money'] += $lostMoney;
                $appUserData['money'] = $sysSettingData['full_money'];
            }else{
                $appUserData['money'] += $appWithdrawData['money'];
            }
            $appUserSubmitData = [
                'unclear_money' => $appUserData['unclear_money'],
                'money' => $appUserData['money'],
            ];
            $appUser->where(['id'=>$appUserData['id']])->update($appUserSubmitData);

            $appWithdraw->where(['id'=>$params['id']])->update($data);
            return json(['msg'=>'审核不通过','status'=>200]);

        }
    }
}