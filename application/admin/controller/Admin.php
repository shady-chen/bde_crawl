<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 21:23
 */

namespace app\admin\controller;




use app\admin\model\AppMoneysteam;
use app\admin\model\AppNotice;
use app\admin\model\Notice;
use app\index\model\AppOrder;
use app\admin\model\SystemBanks;
use app\index\model\AppPacket;
use app\index\model\SystemSetting;
use app\user\controller\Moneysteam;
use app\user\model\AppBanks;
use app\user\model\AppUser;
use app\index\model\AppWithdraw;
use think\Build;


class Admin extends Base
{
    function _initialize()
    {
        if(!$this->isAdmin()){
            return $this->error('您还没有登录！','/admin/user/login');
        }
        //parent::_initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * 数据报表
     */
    public function index()
    {
        //1历史打码量 历史奖励金额
        $moneyStreamModel = new AppMoneysteam();
        $allStreamData = $moneyStreamModel->where(['type'=>'抢红包'])->select();

        $allTotalPacketMoney = 0;
        $allTotalLotteryMoney = 0;
        for($i=0;$i<count($allStreamData);$i++)
        {
            $allTotalPacketMoney  += $allStreamData[$i]['money'];
            $allTotalLotteryMoney += ($allStreamData[$i]['xishu']?$allStreamData[$i]['xishu']:0.006)   * $allStreamData[$i]['money'];
        }
        $this->assign('allTotalPacketMoney',$allTotalPacketMoney);
        $this->assign('allTotalLotteryMoney',$allTotalLotteryMoney);

        //2历史提现总量
        $allWtData = $moneyStreamModel->where(['type'=>'提现'])->select();
        $allWtmoney = 0;
        for($i=0;$i<count($allWtData);$i++)
        {
            $allWtmoney   += $allWtData[$i]['money'];
        }
        $this->assign('allWtmoney',$allWtmoney);

        //3今日打码量  今天奖励总量
        $todaytimestemp = strtotime(date("Y-m-d"), time());

        $da = $moneyStreamModel->where('create_time','>',$todaytimestemp)
            ->where(['type'=>'抢红包'])
            ->select();
        $todayPacketLottery = 0;
        $todayPacketMoney = 0;
        for($i=0;$i<count($da);$i++)
        {
            $todayPacketLottery += ($da[$i]['xishu']?$da[$i]['xishu']:0.006)   * $da[$i]['money'];
            $todayPacketMoney   += $da[$i]['money'];
        }
        $this->assign('todayPacketLottery',$todayPacketLottery);
        $this->assign('todayPacketMoney',$todayPacketMoney);

        //4今日提现总量
        $todayWtData = $moneyStreamModel->where('create_time','>',$todaytimestemp)->where(['type'=>'提现'])->select();
        $todayWtmoney = 0;
        for($i=0;$i<count($todayWtData);$i++)
        {
            $todayWtmoney   += $todayWtData[$i]['money'];
        }
        $this->assign('todayWtmoney',$todayWtmoney);


        //5昨天打码量 昨天奖励总量

        $todaytimestemp = strtotime(date("Y-m-d"), time());
        //获取昨天数据
        $da1 = $moneyStreamModel->where('create_time','<',$todaytimestemp)
            ->where('create_time','>',$todaytimestemp-(3600*24))
            ->where(['type'=>'抢红包'])
            ->select();
        $yesTodayPacketLottery = 0;
        $yesTodayPacketMoney = 0;
        for($i=0;$i<count($da1);$i++)
        {
            $yesTodayPacketLottery += ($da1[$i]['xishu']?$da1[$i]['xishu']:0.006)   * $da1[$i]['money'];
            $yesTodayPacketMoney   += $da1[$i]['money'];
        }

        $this->assign('yesTodayPacketLottery',$yesTodayPacketLottery);
        $this->assign('yesTodayPacketMoney',$yesTodayPacketMoney);

        //6获取昨天提现的钱
        $yesTodayWtData = $moneyStreamModel->where('create_time','<',$todaytimestemp)->where(['type'=>'提现'])
            ->where('create_time','>',$todaytimestemp-(3600*24))
            ->select();
        $yesTodayWtmoney = 0;
        for($i=0;$i<count($yesTodayWtData);$i++)
        {
            $yesTodayWtmoney   += $yesTodayWtData[$i]['money'];
        }
        $this->assign('yesTodayWtmoney',$yesTodayWtmoney);

        //7奖励总量 昨天的奖励量
        $totalLottery= $moneyStreamModel->where(['type'=>'发放奖励'])->select();
        $totalLotteryMoney = 0;
        for($i=0;$i<count($totalLottery);$i++)
        {
            $totalLotteryMoney   += $totalLottery[$i]['money'];
        }
        $this->assign('totalLotteryMoney',$totalLotteryMoney);



        $yesTodayLottery= $moneyStreamModel->where('create_time','>',$todaytimestemp)->where(['type'=>'发放奖励'])
            ->where('create_time','>',$todaytimestemp-(3600*24))
            ->select();
        $yesTodayLotteryMoney = 0;
        for($i=0;$i<count($yesTodayLottery);$i++)
        {
            $yesTodayLotteryMoney   += $yesTodayLottery[$i]['money'];
        }
        $this->assign('yesTodayLotteryMoney',$yesTodayLotteryMoney);






        /********其它数据*****************/
        //1平台会员总数
        $userModel = new AppUser();
        $userData = $userModel->select();
        $userCount = count($userData);
        $this->assign('userCount',$userCount);

        //2今天新增多少用户
        $todayUser = $userModel->where('create_time','>',$todaytimestemp)->select();
        $todayUserCount = count($todayUser);
        $this->assign('todayUserCount',$todayUserCount);

        //3今日打码量最高者
        $todayUserBest = $userModel->order('today_total desc')->find();
        $this->assign('todayUserBest',$todayUserBest);

//dump($todayUserBest);exit;
        return $this->fetch();
    }

    /**
     * 通知列表
     */
    public function article_list(){
        $notice = new AppNotice();
        $title = $this->request->param('title');
        $uid = $this->request->param('uid');
        $state = $this->request->param('state');
        $data = $notice->where('title','like','%'.$title.'%')->where('uid','like','%'.$uid.'%')->where('states','like','%'.$state.'%')->order('create_time desc')->paginate(10);
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
            'create_time'=>time(),
            'read_states'=>0,
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

        $phone = $params['uid'];
        $appUser = new AppUser();
        $user = $appUser->where(['phone'=>$phone])->find();

        $data = [
            'uid'=>$user['id']?$user['id']:0,
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
        $id = $this->request->param('id');
        $expect = $this->request->param('expect');
        $state = $this->request->param('state');

        $time1 = $this->request->param('starttime');
        $time2 = $this->request->param('endtime');



        if($time1 != null && $time1 != '' && $time2 != null && $time2 != '' ){
            $starttime = strtotime($time1.' 00:00:00');
            $endtime = strtotime($time2.' 23:59:59');
            $data = $appOrder->where('create_time','>',$starttime)->where('create_time','<',$endtime)->where('user_phone','like','%'.$phone.'%')->where('id','like','%'.$id.'%')->where('packet_expect','like','%'.$expect.'%')->where('status','like','%'.$state.'%')->order('create_time desc')->paginate(10);

        }else{
            $data = $appOrder->where('user_phone','like','%'.$phone.'%')->where('id','like','%'.$id.'%')->where('packet_expect','like','%'.$expect.'%')->where('status','like','%'.$state.'%')->order('create_time desc')->paginate(10);
        }
        $this->assign('data',$data);
        $this->assign('page',$data->render());
        return $this->fetch();
    }

    public function order_list2(){
        $appOrder = new AppOrder();
        $phone = $this->request->param('phone');
        $id = $this->request->param('id');
        $expect = $this->request->param('expect');
        $state = $this->request->param('state');
        $data = $appOrder->where('user_phone','like','%'.$phone.'%')->where('id','like','%'.$id.'%')->where('packet_expect','like','%'.$expect.'%')->where('status','=',$state)->order('create_time desc')->select();
        $this->assign('data',$data);
        return $this->fetch();
    }

    /**
     * 订单审核弹框
     *
     */
    public function checkOrder(){
        $id = $this->request->param('id');
        $appOrder = new AppOrder();
        $data = $appOrder->where(['id'=>$id])->find();

        return json(['data'=>$data,'status'=>200]);
    }

    /**
     * 订单审核
     */
    public function passOrder(){
        $params = $this->request->param();
        $status = $params['status'];

        $order = new AppOrder();
        $user = new AppUser();
        $money_steam = new AppMoneysteam();
        $setting = new SystemSetting();



        if($status == 4){
            $order->where(['id'=>$params['id']])->update([
                'status'=>4,
            ]);
            return json(['msg'=>'审核不通过','status'=>200]);
        }

        if($status == 3){
            $orderData = $order->where(['id'=>$params['id']])->find();
            $userData = $user->where(['phone'=>$params['user_phone']])->find();
            $settingData = $setting->find();

            $old_money = $userData['money'];
            $old_unclear_money = $userData['unclear_money'];

            $fanAddMoney = $orderData['money']*(1+$settingData['bunus_money']);

            if($userData['money']+$fanAddMoney>$settingData['full_money'])
            {

                $userData['unclear_money'] += $userData['money']+$fanAddMoney-$settingData['full_money'];
                $userData['money'] = $settingData['full_money'];
                $userData['today_total'] += $orderData['money'];

                $user->where(['id'=>$userData['id']])->update([
                    'money'=>$userData['money'],
                    'unclear_money'=>$userData['unclear_money'],
                    //'state'=>2,
                    'today_total'=>$userData['today_total'],
                ]);
                $order->where(['id'=>$params['id']])->update([
                    'status'=>3,
                ]);

                //资金明细

                $remark = '金额增加'.($userData['money']-$old_money).',未结算金额增加'.($userData['unclear_money']-$old_unclear_money);

                $money_steam->save([
                    'money'=>$orderData['money'],
                    'user_money_now'=>$old_money,
                    'user_money_later'=>$userData['money'],
                    'remark'=>$remark,
                    'uid'=>$orderData['uid'],
                    'create_time'=>time(),
                    'xishu'=>$settingData['bunus_money'],
                    'type'=>'抢红包',
                ]);


                return json(['msg'=>'审核通过','status'=>200]);
            }else{
                $userData['money'] += $fanAddMoney;

                $userData['today_total'] += $orderData['money'];
                $user->where(['id'=>$userData['id']])->update([
                    'money'=>$userData['money'],
                    'today_total'=>$userData['today_total'],
                ]);

                $order->where(['id'=>$params['id']])->update([
                    'status'=>3,
                ]);

                //资金明细
                $remark = '金额增加'.($userData['money']-$old_money);

                $money_steam->save([
                    'money'=>$orderData['money'],
                    'user_money_now'=>$old_money,
                    'user_money_later'=>$userData['money'],
                    'remark'=>$remark,
                    'uid'=>$orderData['uid'],
                    'create_time'=>time(),
                    'xishu'=>$settingData['bunus_money'],
                    'type'=>'抢红包',
                ]);

                return json(['msg'=>'审核通过','status'=>200]);
            }
        }



        $order->where(['id'=>$params['id']])->update([
            'status'=>$status,
        ]);

        return json(['msg'=>'修改成功','status'=>200]);

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
//        if($params['star_time']<0 || $params['star_time']>=$params['end_time'] || $params['end_time']>23){
//            return json(['msg'=>'开始时间不能大于结束时间','status'=>0]);
//        }
        if($params['per_total']<=0){
            return json(['msg'=>'发包总金额不能小于0','status'=>0]);
        }
        if($params['minManey']<=0){
            return json(['msg'=>'每个红包的最小金额不能小于0','status'=>0]);
        }
        if($params['minManey']>$params['maxManey']){
            return json(['msg'=>'最小金额不能大于最大金额','status'=>0]);
        }

        if($params['minManey']>$params['per_total']/$params['how_many']){
            return json(['msg'=>'每个红包的最小金额不能大于总金额的平均数','status'=>0]);
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
            'minManey'=>$params['minManey'],
            'maxManey'=>$params['maxManey'],
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
        $invitation_code = $this->request->param('invitation_code');
        $state = $this->request->param('state');
//        if($phone != null && $phone != ''){
//            $data = $appUser->where('phone','like','%'.$phone.'%')->order('create_time desc')->paginate(10);
            $data = $appUser->where(['type'=>1])->where('state','like','%'.$state.'%')->where('phone','like','%'.$phone.'%')->where('invitation_code','like','%'.$invitation_code.'%')->order('create_time desc')->paginate(10);


//        }else{
//            $data = $appUser->where(['type'=>1])->order('create_time desc')->paginate(10);
//        }
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
        $id = $this->request->param('id');
        $state = $this->request->param('state');
        $data = $appWithdraw->where('user_phone','like','%'.$phone.'%')->where('id','like','%'.$id.'%')->where('states','like','%'.$state.'%')->order('create_time desc')->paginate(10);
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

        $money_steam = new AppMoneysteam();

        $appWithdraw = new AppWithdraw();

        //审核通过时
        if($status == 2){
            $data = [
                'states'=>$params['states'],
                //'admin_remark'=>$params['admin_remark']
            ];
            $appWithdraw->where(['id'=>$params['id']])->update($data);
            return json(['msg'=>'审核通过','status'=>200]);
        }

        //审核不通过时
        if($status == 3){
            $data = [
                'states'=>$params['states'],
                //'admin_remark'=>$params['admin_remark']
            ];

            $appUser = new AppUser();
            $appUserData = $appUser->where(['phone'=>$params['user_phone']])->find();

            $appWithdrawData = $appWithdraw->where(['id'=>$params['id']])->find();

            $sysSetting = new SystemSetting();
            $sysSettingData = $sysSetting->find();

            $old_money = $appUserData['money'];
//            $old_unclear_money = $appUserData['unclear_money'];

            if($appUserData['money']+$appWithdrawData['money']>$sysSettingData['full_money']){
                $lostMoney = $appUserData['money']+$appWithdrawData['money']-$sysSettingData['full_money'];
                $appUserData['unclear_money'] += $lostMoney;
                $appUserData['money'] = $sysSettingData['full_money'];

                //资金明细

                $remark = '金额增加'.($appUserData['money']-$old_money).',未结算金额增加'.$lostMoney;

                $money_steam->save([
                    'money'=>$appWithdrawData['money'],
                    'user_money_now'=>$old_money,
                    'user_money_later'=>$appUserData['money'],
                    'remark'=>$remark,
                    'uid'=>$appWithdrawData['uid'],
                    'create_time'=>time(),
                    'type'=>'提现审核不通过',
                ]);

            }else{
                $appUserData['money'] += $appWithdrawData['money'];
                //资金明细

                $remark = '金额增加'.$appWithdrawData['money'].',未结算金额增加0';

                $money_steam->save([
                    'money'=>$appWithdrawData['money'],
                    'user_money_now'=>$old_money,
                    'user_money_later'=>$appUserData['money'],
                    'remark'=>$remark,
                    'uid'=>$appWithdrawData['uid'],
                    'create_time'=>time(),
                    'type'=>'提现审核不通过',
                ]);
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


    /*
     * 游戏规则
     */
    public function rules(){

        $setting = new SystemSetting();
        $data = $setting->where(['id'=>1])->find();
        $this->assign('data',$data);

        return $this->fetch();
    }
    /*
     * 游戏规则
     */
    public function update_rules(){

        $setting = new SystemSetting();
        $params = $this->request->param();
        $res = $setting->where(['id'=>1])->update(['text_rules'=>$params['rules']]);
        if($res){
            return json(['status'=>200]);
        }else{
            return json(['status'=>0]);
        }
    }

    /**
     * 红包列表页面
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function redpacket_list(){


        $packetMonel =  new AppPacket();

        $data = $packetMonel->order('create_time desc')->paginate(10);
        $this->assign('data',$data);
        $this->assign('page',$data->render());
        return $this->fetch();
    }




    /**
     * 订单详情页面
     *
     */
    public function checkPacket(){
        $id = $this->request->param('id');
        $appOrder = new AppOrder();
        $data = $appOrder->where(['packet_id'=>$id])->select();
        $this->assign('data',$data);
        return $this->fetch();

    }


    /**
     * 管理员密码修改页面
     * @return array
     */
    public function update_pwd()
    {
        return $this->fetch();
    }

    /**
     * 管理员密码修改api
     */
    public function update_pwd2()
    {
        $old = $this->request->param('old');
        $new = $this->request->param('new');
        $userModel = new AppUser();
        $admin = $userModel->where(['phone'=>'admin'])->find();

        if(md5($old) != $admin['password']){
            return json(['status'=>0,'msg'=>'旧密码不正确！']);
        }else{
            $userModel->where(['phone'=>'admin'])->update(['password'=>md5($new)]);
            session('admin',null);
            return json(['status'=>200,'msg'=>'修改成功！']);
        }

    }


    /**
     * 定时更新订单 提示音
     */
    public function newOrder()
    {
        $orderModel = new AppOrder();
        $withdrawOrderModel = new AppWithdraw();

        $newOrder = $orderModel->where('create_time','>',time()-60)->find();

        $newWithdraw = $withdrawOrderModel->where('create_time','>',time()-60)->find();

        $data = [
            'order'=>$newOrder,
            'withdraw'=>$newWithdraw,
        ];


        return json($data);


    }


    /**
     * 更新管理员的备注
     */
    public function update_remark(){
        $params = $this->request->param();
        $appWithdraw = new AppWithdraw();
        $data = [
            'admin_remark'=>$params['admin_remark']
        ];
        $appWithdraw->where(['id'=>$params['id']])->update($data);
        return json(['msg'=>'审核通过','status'=>200]);
    }







    /**
     * 查看用户的资金流水
     */
     public function show_user_money_steam(){

         $id = $this->request->param('id');
         $user_phone = $this->request->param('user_phone');
         $steamModel = new AppMoneysteam();
         $data = $steamModel->where(['uid'=>$id])->limit(30)->order('create_time desc')->select();




         /************************************历史记录开始***************************************************/
         //获取所有抢红包的钱
         $allPacketMoneyToday = $steamModel->where(['uid'=>$id])->where(['type'=>'抢红包'])->select();
         //历史奖励总量
         $allPacketLottery = 0;
         //历史打码总量
         $allPacketMoney = 0;

         for($i=0;$i<count($allPacketMoneyToday);$i++)
         {

             $allPacketLottery += ($allPacketMoneyToday[$i]['xishu']?$allPacketMoneyToday[$i]['xishu']:0.006)   * $allPacketMoneyToday[$i]['money'];
             $allPacketMoney   += $allPacketMoneyToday[$i]['money'];
         }



         //获取所有提现的钱
         $allWtData = $steamModel->where(['uid'=>$id])->where(['type'=>'提现'])->select();
         $allWtmoney = 0;
         for($i=0;$i<count($allWtData);$i++)
         {
             $allWtmoney   += $allWtData[$i]['money'];
         }


         /************************************历史记录结束***************************************************/



         /************************************今日记录开始***************************************************/

         //获得当日0点的时间戳
         $todaytimestemp = strtotime(date("Y-m-d"), time());

         $da = $steamModel->where(['uid'=>$id])->where('create_time','>',$todaytimestemp)
             ->where(['type'=>'抢红包'])
             ->select();
         //今天奖励总量
         $todayPacketLottery = 0;
         //今天打码总量
         $todayPacketMoney = 0;
         for($i=0;$i<count($da);$i++)
         {

             $todayPacketLottery += ($da[$i]['xishu']?$da[$i]['xishu']:0.006)   * $da[$i]['money'];

             $todayPacketMoney   += $da[$i]['money'];
         }



         //获取今天提现的钱
         $todayWtData = $steamModel->where(['uid'=>$id])->where('create_time','>',$todaytimestemp)->where(['type'=>'提现'])->select();
         $todayWtmoney = 0;
         for($i=0;$i<count($todayWtData);$i++)
         {
             $todayWtmoney   += $todayWtData[$i]['money'];
         }


         /************************************今日记录结束***************************************************/


         /************************************昨天记录开始***************************************************/

         //获得当日0点的时间戳
         $todaytimestemp = strtotime(date("Y-m-d"), time());
         //获取昨天数据
         $da1 = $steamModel->where(['uid'=>$id])->where('create_time','<',$todaytimestemp)
             ->where('create_time','>',$todaytimestemp-(3600*24))
             ->where(['type'=>'抢红包'])
             ->select();
         //昨天奖励总量
         $yesTodayPacketLottery = 0;
         //昨天打码总量
         $yesTodayPacketMoney = 0;
         for($i=0;$i<count($da1);$i++)
         {
             $yesTodayPacketLottery += ($da1[$i]['xishu']?$da1[$i]['xishu']:0.006)   * $da1[$i]['money'];
             $yesTodayPacketMoney   += $da1[$i]['money'];
         }

         //获取昨天提现的钱
         $yesTodayWtData = $steamModel->where(['uid'=>$id])->where('create_time','<',$todaytimestemp)->where(['type'=>'提现'])
             ->where('create_time','>',$todaytimestemp-(3600*24))
             ->select();
         $yesTodayWtmoney = 0;
         for($i=0;$i<count($yesTodayWtData);$i++)
         {
             $yesTodayWtmoney   += $yesTodayWtData[$i]['money'];
         }


         /************************************昨天记录结束***************************************************/



         //数据渲染

         //历史奖励总量
         $this->assign('allPacketLottery',$allPacketLottery);
         //历史打码总量
         $this->assign('allPacketMoney',$allPacketMoney);
         //获取所有提现的钱
         $this->assign('allWtmoney',$allWtmoney);

         //今天奖励总量
         $this->assign('todayPacketLottery',$todayPacketLottery);
         //今天打码总量
         $this->assign('todayPacketMoney',$todayPacketMoney);
         //获取今天提现的钱
         $this->assign('todayWtmoney',$todayWtmoney);

         //昨天奖励总量
         $this->assign('yesTodayPacketLottery',$yesTodayPacketLottery);
         //昨天打码总量
         $this->assign('yesTodayPacketMoney',$yesTodayPacketMoney);
         //获取昨天提现的钱
         $this->assign('yesTodayWtmoney',$yesTodayWtmoney);

         $this->assign('data',$data);
         $this->assign('user_phone',$user_phone);
         return $this->fetch();
     }










}