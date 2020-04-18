<?php

namespace app\index\controller;


use think\Controller;



class Index extends Controller
{

    public function index()
    {

        $return_data = [];
        $website_list = file_get_contents("http://us.shopnm.top/showDir.php");
        $website_list = json_decode($website_list, true);
        $count = count($website_list);
        $insertCount = 0;
        foreach ($website_list as $key=> $value)
        {
            if(!db('website_list')->where(['domain'=>substr($value,11)])->find())
            {
                db('website_list')->insert(['domain'=>substr($value,11),"create_time"=>time()]);
                $insertCount++;
            }
            else
            {

            }
            $return_data[$key] = [
                'domain'=>substr($value,11),
                'count'=>0,
                'downloaded'=>0,
                'new'=>0,
            ];
            ob_flush();
        }
        flush();
        $existCount= $count-$insertCount;
        ob_flush();
        flush();

        $file_count = 0;
        $file_insert_count = 0;
        foreach ($website_list as $key=> $value)
        {
            $file_list = file_get_contents("http://us.shopnm.top/showFiles.php?dir=/".substr($value,11));
            $file_list = json_decode($file_list, true);

            $per_website_count = 0;
            $return_data[$key]['count'] = count($file_list);
            foreach ($file_list as $k=>$v)
            {

                $number = 11+strlen(substr($value,11))+1;
                if(!db('file_name')->where(['file_name'=>substr($v,$number)])->find())
                {
                    db('file_name')->insert(['file_name'=>substr($v,$number),"belong"=>substr($value,11),"create_time"=>time()]);
                    $file_insert_count++;
                    $return_data[$key]['new'] = $return_data[$key]['new']+1;
                }
                else
                {
                    $return_data[$key]['downloaded'] = $return_data[$key]['downloaded']+1;
                }
                $file_count++;

            }
        }
        $file_exist_count = $file_count - $file_insert_count;
        return json($return_data);
        //到此目录已完成

    }





}
