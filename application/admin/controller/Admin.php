<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 21:23
 */

namespace app\admin\controller;




class Admin extends Base
{
    public function index(){
        return $this->fetch();
    }


    public function article_list(){
        return $this->fetch();
    }

    public function article_add(){
        return $this->fetch();
    }



    public function login(){

    }
}