<?php
// 需要先安装 workerman,
// composer require walkor/workerman
// 需要在网卡中配置公网ip地址
// 由于udp转发，由于每次只能监听一个端口，等于没啥用。



use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\TcpConnection;
require_once __DIR__ . '/vendor/autoload.php';

//公网ip
$public_ip = '1.1.1.1';

// 内网ip  网卡上的ip
$network_card_ip = '172.16.64.6';

//监听端口
$port = 10001;
$worker = new Worker('udp://0.0.0.0:'.$port);

$worker->count = 1;

// tcp连接建立后
$worker->onMessage = function(\Workerman\Connection\UdpConnection $connection,$data) use($port)
{
    global $public_ip;
    
    $connection2 = new \Workerman\Connection\AsyncUdpConnection('udp://'.$public_ip.':'.$port);
    $connection2->onMessage     = function ($source, $data) use ($connection,$connection2) {
        $connection->close($data);
        $connection->close();
    };

    $connection2->onConnect     = function () use ($connection2,$data) {
        $connection2->send($data);
    };

    // 执行异步连接
    $connection2->connect();
};

// 运行worker
Worker::runAll();
