<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 12:41
 */

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;


class Test extends Command
{
    protected function configure()
    {
        $this->setName('Test')->setDescription('Here is the mikkle\'s command ');
    }


    protected function execute(Input $input, Output $output)
    {
        echo "start to get website list........\n";

        $website_list = file_get_contents("http://us.shopnm.top/showDir.php");
        $website_list = json_decode($website_list, true);
        $count = count($website_list);
        echo "Total ----" . $count . " website-----\n";
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
        }
        $existCount= $count-$insertCount;
        echo "Insert {$insertCount} and {$existCount} already exist\n";
        echo "\n";

        echo "start to get FILE NAME\n";


        $file_count = 0;
        $file_insert_count = 0;
        foreach ($website_list as $key=> $value)
        {
            $file_list = file_get_contents("http://us.shopnm.top/showFiles.php?dir=/".substr($value,11));
            $file_list = json_decode($file_list, true);
            foreach ($file_list as $k=>$v)
            {

                $number = 11+strlen(substr($value,11))+1;
                if(!db('file_name')->where(['file_name'=>substr($v,$number)])->find())
                {
                    db('file_name')->insert(['file_name'=>substr($v,$number),"belong"=>substr($value,11),"create_time"=>time()]);
                    $file_insert_count++;
                }
                else
                {

                }
                $file_count++;
            }
        }
        $file_exist_count = $file_count - $file_insert_count;
        echo "Total ----" . $file_count . " data-----\n";
        echo "....\n";
        echo "....\n";
        echo "....\n";
        echo "....\n";
        echo "Insert {$file_insert_count} and {$file_exist_count} already exist\n";
        echo "....\n";
        echo "....\n";
        echo "END----------------------------------------------------\n";
        //到此目录已完成
    }


}