<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 21:26
 */

namespace app\admin\controller;


use think\Controller;

class Base extends Controller
{
    public function isAdmin(){

        $admin = session('admin');
        if(!$admin){
            return false;
        }
        if($admin['phone'] != 'admin'){
            return false;
        }

        if($admin['type'] != 0){
            return false;
        }

        return true;

    }
}