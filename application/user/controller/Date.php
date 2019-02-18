<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 20:38
 */

namespace app\user\controller;

use app\index\controller\Index;
use app\index\model\AppOrder;

/**
 * 用户数据类
 * @package app\user\controller
 */
class Date extends Index
{
    /**
     * 用户获取自己的订单信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrder()
    {
        $user = session('user');
        $params = $this->request->param();
        $appOrder = new AppOrder();
        if ($params['status'] != null && $params['status'] != '') {
            $data = $appOrder->where(function ($query) USE ($user, $params) {
                $query->where('status', $params['status'])->whereOr('status', $params['status']);
            })->where(['uid' => $user['id']])->order('create_time desc')->select();
//            $data = $appOrder->where(['uid'=>$user['id'],'status'=>$params['status']])->order('create_time desc')->select();
        } else {
            $data = $appOrder->where(['uid' => $user['id'], 'status' => $params['status']])->order('create_time desc')->select();

        }
        return json(['data' => $data, 'status' => 200]);
    }


    /**
     * 用户获取自己今天的订单信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderToday()
    {
        $user = session('user');
        $todaytimestemp = strtotime(date("Y-m-d"), time());
        $appOrder = new AppOrder();
        $data = $appOrder->where("create_time",">",$todaytimestemp)->where("create_time","<",$todaytimestemp + 60*60*24 - 1)->where(['uid' => $user['id']])->order('create_time desc')->select();

        return json(['data' => $data, 'status' => 200]);
    }


    /**
     * 用户获取自己2小时内的订单信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrder2Hour()
    {
        $user = session('user');
        $appOrder = new AppOrder();
        $data = $appOrder->where("create_time",">",time() - 60*60*2)->where(['uid' => $user['id'],'status'=>1])->order('create_time desc')->select();
        return json(['data' => $data, 'status' => 200]);
    }


    /**
     * 用户获取订单详情页面
     */

    public function oneOrder()
    {
        $order_id = $this->request->param('order_id');
        $user = session('user');
        $appOrder = new AppOrder();
        $data = $appOrder->where(['uid' => $user['id'], 'id' => $order_id])->find();
        return json(['data' => $data, 'status' => 200]);
    }


    /**
     * 上传凭证的接口
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function changeOrderState()
    {
        $user = session('user');
        $uid = $user['id'];

        $orderId = $this->request->param('order_id');
        $remark = $this->request->param('remark');
        $appOrder = new AppOrder();
        $order = $appOrder->where(['id' => $orderId])->find();
        if (!$order) {
            return json(['msg' => '订单异常！', 'status' => 0]);
        }
//        if ($order['status'] == 0) {
//            return json(['msg' => '订单已过期！', 'status' => 0]);
//        }
//        if ($order['status'] == 4) {
//            return json(['msg' => '订单审核不通过 无法再提交！', 'status' => 0]);
//        }

        if ($order['status'] == 1) {
            // 获取表单上传文件
            $file = request()->file('img_file');
            if (empty($file)) {
                return json(['msg' => '请选择上传您的凭证文件', 'status' => 0]);
            }
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upload' . DS);
            //如果不清楚文件上传的具体键名，可以直接打印$info来查看
            //获取文件（文件名），$info->getFilename()  ***********不同之处，笔记笔记哦
            //获取文件（日期/文件名），$info->getSaveName()  **********不同之处，笔记笔记哦
            $filename = $info->getSaveName();  //在测试的时候也可以直接打印文件名称来查看
            if (!$filename) {
                return json(['msg' => $file->getError(), 'status' => 0]);
            }

            //更新订单
            $appOrder->where(['id' => $orderId])->update([
                'status' => 2,
                'img_url' => DS . 'upload\\' . $filename,
                'remarks' => $remark,
            ]);

            return json(['msg' => '提交成功，请等待审核', 'status' => 200]);
        }else{
            return json(['msg' => '订单已操作过或已过期，请勿重复提交！', 'status' => 0]);
        }

    }




    /**
     * 上传凭证的接口
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function OrderTogether()
    {
        $user = session('user');
        $uid = $user['id'];
        $appOrder = new AppOrder();
        $orderId = $this->request->param('order_id');
        $ids = explode(",", $orderId );
        $remark = $this->request->param('remark');
        $money = $this->request->param('money');
        $sys_bank_num = $this->request->param('sys_bank_num');
        $sys_bank_which = $this->request->param('sys_bank_which');
        $sys_bank_where = $this->request->param('sys_bank_where');
        $sys_name = $this->request->param('sys_name');


        //是否已存在这订单
        $isExits = $appOrder->where([
            'uid'=>$user['id'],
            'packet_id'=>88888,
            'user_phone'=>$user['phone'],
            'packet_expect'=>88888,
            'money'=>$money,
            'status'=>2,
            'remarks' => $remark,
            'sys_bank_num'=>$sys_bank_num,
            'sys_bank_which'=>$sys_bank_which,
            'sys_bank_where'=>$sys_bank_where,
            'sys_name'=>$sys_name,
        ])->find();
        if($isExits)
        {
            return json(['msg' => '订单已提交，请勿重复提交！', 'status' => 0]);
        }



        //更新合并的订单
        for($i=0;$i<count($ids);$i++)
        {
            $appOrder->where(['id' => $ids[$i]])->update([
                'status' => 0,
                'remarks' => $remark,
            ]);
        }



        $order = $appOrder->where(['id' => $orderId])->find();
        if (!$order) {
            return json(['msg' => '订单异常！', 'status' => 0]);
        }



        // 获取表单上传文件
        $file = request()->file('img_file');
        if (empty($file)) {
            return json(['msg' => '请选择上传您的凭证文件', 'status' => 0]);
        }
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'upload' . DS);
        //如果不清楚文件上传的具体键名，可以直接打印$info来查看
        //获取文件（文件名），$info->getFilename()  ***********不同之处，笔记笔记哦
        //获取文件（日期/文件名），$info->getSaveName()  **********不同之处，笔记笔记哦
        $filename = $info->getSaveName();  //在测试的时候也可以直接打印文件名称来查看
        if (!$filename) {
            return json(['msg' => $file->getError(), 'status' => 0]);
        }




        //更新订单
        $appOrder->save([
            'uid'=>$user['id'],
            'packet_id'=>88888,
            'user_phone'=>$user['phone'],
            'packet_expect'=>88888,
            'money'=>$money,
            'status'=>2,
            'img_url' => DS . 'upload\\' . $filename,
            'remarks' => $remark,
            'create_time'=>time(),
            'sys_bank_num'=>$sys_bank_num,
            'sys_bank_which'=>$sys_bank_which,
            'sys_bank_where'=>$sys_bank_where,
            'sys_name'=>$sys_name,
        ]);

        return json(['msg' => '提交成功，请等待审核', 'status' => 200]);


    }


}