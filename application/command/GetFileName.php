<?php


namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;





class GetFileName extends Command
{
    protected function configure()
    {
        $this->setName('GetFileName')->setDescription('Here is the mikkle\'s command ');
    }


    protected function execute(Input $input, Output $output)
    {

        echo "start to get data content one by one.......\n";

        $file_name_list = db('file_name')->where(['collected'=>0])->select();

        $data_count = count($file_name_list);
        $insert_count = 0;
        echo "Total {$data_count} data.......\n";

        foreach ($file_name_list as $key=>$value)
        {
            $content =  file_get_contents("http://us.shopnm.top/ordersdb/".$value['belong']."/".$value['file_name']);

            if(!db('order_content')->where(['file_name'=>$value['file_name']])->find())
            {
                db('order_content')->insert(['file_name'=>$value['file_name'],"belong"=>$value['belong'],"content"=>$content,"create_time"=>time()]);
                db('file_name')->where(['file_name'=>$value['file_name']])->update(['collected'=>1]);
                $insert_count++;
                echo $insert_count." be inserted....\n";
            }
        }
        $file_exist_count = $data_count - $insert_count;

        echo "....\n";
        echo "....\n";
        echo "....\n";
        echo "....\n";
        echo "Insert {$insert_count} and {$file_exist_count} already exist\n";


    }


}