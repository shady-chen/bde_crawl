<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 12:41
 */

namespace app\command;


use app\index\controller\Index;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\index\model\SystemSetting;
use app\index\model\AppPacket;
use Workerman\Worker;
use \Workerman\Lib\Timer;

require_once __DIR__ . '/Workerman/Autoloader.php';


class Test extends Command
{
    protected function configure()
    {
        $this->setName('Test')->setDescription('Here is the mikkle\'s command ');
    }

    /**
     * 获取发包数据 规则
     */
    protected function init()
    {
        $SystemSetting = new SystemSetting();
        $data = $SystemSetting->where('id', 1)->find();
        return $data;
    }

    protected function startWorkman()
    {
        $http_worker = new Worker("websocket://0.0.0.0:2345");

        // 启动4个进程对外提供服务
        $http_worker->count = 4;

        // 接收到浏览器发送的数据时回复hello world给浏览器
        $http_worker->onMessage = function ($connection, $data) {
            echo $data . "\n";
            // 向浏览器发送hello world
            $connection->send('hello world');

        };

        /************************定时任务*******************************/
        //$appPacket = new AppPacket();要写数据库的话 这行代码要放在while循环中


        $http_worker->onWorkerStart = function ($http_worker) {
            // 每2.5秒执行一次 支持小数，可以精确到0.001，即精确到毫秒级别

            //获取系统设置的发包时间

            $time_interval = 1;
            Timer::add($time_interval, function () use ($http_worker) {
                $data = $this->init();
                //$data = $data[0];

                //获得当日0点的时间戳
                $todaytimestemp = strtotime(date("Y-m-d"), time());
                //获取开始发包的时间戳
                $startime = $data['star_time'] * 60 * 60;

                if($data['end_time'] - $data['star_time'] >0){
                    $endtime = $data['end_time'] * 60 * 60;
                }else{
                    $endtime = ($data['end_time']+24) * 60 * 60;
                }
                //现在的时间戳
                $now = time();


                $index = new Index();
                //凌晨一点清除所有的打码量！！！！！
                if($now == $todaytimestemp + (60 * 60 * 12 + 36*60 ))
                {
                    echo "start to clear all of total_total\n";
                    $index->award();
                    echo "all today_total have been clean!\n";
                }

                //修改过期订单状态
                $index->updateOrder();


                //时间对比 达到刚好发包时间时 发包
                $appPacket = new AppPacket(); //要写数据库的话 这行代码要放在while循环中
                for ($i = 0; $i < (($endtime - $startime) / ($data['how_long'])); $i++) {
                    if ($now == ($todaytimestemp + $startime) + ($i * $data['how_long'])) {
                        $e = $i + 1;
                        if ($e < 10) {
                            $e = '000' . $e;
                        }
                        if ($e >= 10 && $e < 100) {
                            $e = '00' . $e;
                        }
                        if ($e >= 100 && $e < 1000) {
                            $e = '0' . $e;
                        }
                        $appPacket->save([
                            'expect' => date("Y") . date("m") . date("d") . $e,
                            'money' => $data['per_total'],
                            'amount' => $data['how_many'],
                            'create_time' => time()
                        ]);
                        echo(date("Y") . date("m") . date("d") . ($i + 1) . " have created!\n");

                        /**
                         * 是否发给前端
                         */
                       /* foreach ($http_worker->connections as $connection) {

                            $connection->send(json_encode([
                                'expect' => date("Y") . date("m") . date("d") . $e,
                                'money' => $data['per_total'],
                                'amount' => $data['how_many'],
                                'create_time' => time()
                            ]));

                        }*/
                        break;
                    } else {

                        //超过时间的时候怎么处理  找到离他最近的一期 进行对比
                        if ((($todaytimestemp + $startime) + ($i * $data['how_long'])) - $now > -$data['how_long'] &&
                            (($todaytimestemp + $startime) + ($i * $data['how_long'])) - $now < 0) {
                            echo "still have " . ((($todaytimestemp + $startime) + (($i + 1) * $data['how_long'])) - $now) . "\n";
                            break;
                        }

                    }
                }
            });
        };


        // 运行worker
        Worker::runAll();
    }


    protected function fabao()
    {


        /************************定时任务*******************************/
        //$appPacket = new AppPacket();要写数据库的话 这行代码要放在while循环中


        // 每2.5秒执行一次 支持小数，可以精确到0.001，即精确到毫秒级别

        //获取系统设置的发包时间

        $time_interval = 1;

        $data = $this->init();
        //$data = $data[0];

        //获得当日0点的时间戳
        $todaytimestemp = strtotime(date("Y-m-d"), time());
        $startime = $data['star_time'] * 60 * 60;
        $endtime = $data['end_time'] * 60 * 60;
        //现在的时间戳
        $now = time();
        //时间对比 达到刚好发包时间时 发包
        $appPacket = new AppPacket(); //要写数据库的话 这行代码要放在while循环中
        for ($i = 0; $i < (($endtime - $startime) / ($data['how_long'])); $i++) {
            if ($now == ($todaytimestemp + $startime) + ($i * $data['how_long'])) {
                $e = $i + 1;
                if ($e < 10) {
                    $e = '000' . $e;
                }
                if ($e >= 10 && $e < 100) {
                    $e = '00' . $e;
                }
                if ($e >= 100 && $e < 1000) {
                    $e = '0' . $e;
                }
                $appPacket->save([
                    'expect' => date("Y") . date("m") . date("d") . $e,
                    'money' => $data['per_total'],
                    'amount' => $data['how_many'],
                    'create_time' => time()
                ]);
                echo(date("Y") . date("m") . date("d") . ($i + 1) . " have created!\n");

                break;
            } else {

                //超过时间的时候怎么处理  找到离他最近的一期 进行对比
                if ((($todaytimestemp + $startime) + ($i * $data['how_long'])) - $now > -$data['how_long'] &&
                    (($todaytimestemp + $startime) + ($i * $data['how_long'])) - $now < 0) {
                    echo "still have " . ((($todaytimestemp + $startime) + (($i + 1) * $data['how_long'])) - $now) . "\n";
                    break;
                }

            }
        }


    }

    protected function execute(Input $input, Output $output)
    {
        $this->startWorkman();
    }


}