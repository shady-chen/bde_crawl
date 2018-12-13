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
    public function getOrder(){
        $user = session('user');
        $appOrder = new AppOrder();
        $data = $appOrder->where(['uid'=>$user['id']])->select();
        return json($data);
    }



    /**
     * 用户获取订单详情页面
     */

    public function oneOrder(){
       $order_id = $this->request->param('order_id');
       $user = session('user');
       $appOrder = new AppOrder();
       $data = $appOrder->where(['uid'=>$user['id'],'id'=>$order_id])->find();
       return json($data);
    }


    /**
     * 上传凭证的接口
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function changeOrderState(){
        $user = session('user');
        $uid= $user['id'];

        $orderId = $this->request->param('order_id');
        $appOrder = new AppOrder();
        $order = $appOrder->where(['id'=>$orderId])->find();
        if(!$order){
            return json(['msg'=>'订单异常！','status'=>0]);
        }
        if($order['status'] == 0){
            return json(['msg'=>'订单已过期！','status'=>0]);
        }
        if($order['status'] == 4){
            return json(['msg'=>'订单审核不通过 无法再提交！','status'=>0]);
        }

        if($order['status'] == 1)
        {
            // 获取表单上传文件
            $file = request()->file('img_file');
            if(empty($file))
            {
                return json(['msg'=>'请选择上传您的凭证文件','status'=>0]);
            }
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH.'public'.DS.'upload');
            //如果不清楚文件上传的具体键名，可以直接打印$info来查看
            //获取文件（文件名），$info->getFilename()  ***********不同之处，笔记笔记哦
            //获取文件（日期/文件名），$info->getSaveName()  **********不同之处，笔记笔记哦
            $filename = $info->getSaveName();  //在测试的时候也可以直接打印文件名称来查看
            if(!$filename)
            {
                return json(['msg'=>$file->getError(),'status'=>0]);
            }

            //更新订单
            $appOrder->where(['id'=>$orderId])->update([
                'status'=>2,
                'img_url'=>ROOT_PATH.'public'.DS.'upload' . $filename
            ]);

            return json(['msg'=>'提交成功，请等待审核','status'=>200]);


        }

    }














}