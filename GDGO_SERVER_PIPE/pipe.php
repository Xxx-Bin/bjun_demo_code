<?php
// 需要先安装 workerman
// composer require walkor/workerman



use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\TcpConnection;
require_once __DIR__ . '/vendor/autoload.php';

//公网ip
$public_ip = '1.1.1.1';

// agent 监听的 500x 的 ip
$agent_listen_ip = '127.0.0.1';

// 内网ip  网卡上的ip
$network_card_ip = '172.16.64.6';

$worker = new Worker('tcp://'.$network_card_ip.':5001');
$worker->count = 2;

// tcp连接建立后
$worker->onConnect = function(TcpConnection $connection)
{

    global $public_ip;
    global $agent_listen_ip;
    global $network_card_ip;


    $public_ip_raw = implode('',array_map(function($v){return chr($v);},explode('.',$public_ip)));
    $network_card_ip_raw = implode('',array_map(function($v){return chr($v);},explode('.',$network_card_ip)));

    $connection_to_80 = new AsyncTcpConnection('tcp://'.$agent_listen_ip.':5001');

    $connection->pipe($connection_to_80);


    $connection_to_80->pipe($connection);
    $connection_to_80->onMessage     = function ($source, $data) use ($connection,$network_card_ip_raw,$public_ip_raw) {

        if(substr($data,-8,6) == (chr(0).chr(0).$network_card_ip_raw)){
            $d = substr($data,-6);
            var_dump(unpack('H*',$d));
            $data = substr_replace($data,$public_ip_raw.substr($data,-2),-6);
            $d = substr($data,-6);
            var_dump(unpack('H*',$d));
        }



        $connection->send($data);
    };
    // 执行异步连接
    $connection_to_80->connect();
};

// 运行worker
Worker::runAll();
