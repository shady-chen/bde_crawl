<?php


namespace app\admin\controller;


/**
 * Class Customer
 * @package app\admin\controller
 */
class Customer extends Base
{


    public function index()
    {
        $map = [];
        //国家
        if(isset($_GET['country']) && !empty($_GET['country']))
        {
            $map['country'] = ['like',"%{$_GET['country']}%"];
        }
        //人名
        if(isset($_GET['username']) && !empty($_GET['username']))
        {
            $map['all_name'] = ['like',"%{$_GET['username']}%"];
        }
        //电话
        if(isset($_GET['phone']) && !empty($_GET['phone']))
        {
            $map['phone'] = ['like',"%{$_GET['phone']}%"];
        }
        $data = db('customer')->where($map)->paginate(3,false,['query'=>$_GET]);
        $this->assign('data',$data);
        $this->assign('page',$data->render());
        return $this->fetch();
    }



}