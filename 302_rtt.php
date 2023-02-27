<?php

ini_set('dispaly_errors','stdout');
ini_set('error_reporting',0);

define('DS',DIRECTORY_SEPARATOR);
define('XPATH',__DIR__.DS);
define('XPATH_LOG', XPATH . DS.'log'. DS);
class XEz{
    public static function log($type,$text,$format = 'var_export')
    {
        $type = strtr(trim($type,'/\\'),['./'=>'','../'=>'','.\\'=>'','..\\'=>'','\\'=>DS]);
        $path = explode(DS,$type);
        $file = array_pop($path);
        $path_file = XPATH_LOG;
        if(!empty($path)){
            foreach ($path as $p){
                $path_file .= DS.$p;
                if(!file_exists($path_file)){
                    mkdir($path_file);
                }
            }
        }
        file_put_contents($path_file.DS.$file.'.log',XEz::date_ez(time()).' '.(is_array($text)?($format=='var_export'?var_export($text,1):json_encode($text,JSON_UNESCAPED_UNICODE)):$text).PHP_EOL,FILE_APPEND);
    }
    public static function date_ez($timestamp=null,$format='Y-m-d H:i:s',$defualt = '-') {
        return empty($timestamp)?$defualt:date($format,$timestamp);
    }
}

if(!empty($_GET['token'])){
    require_once __DIR__.'/../lib/ProxyGuess.php';
    $vpn_mss_ipv4_arr = require_once 'vpn_mss_ipv4_arr.php';
    require_once '_out.php';
    require_once 'DataShareClientForWeb.php';
    $global = new DataShareClient('127.0.0.1:2207');
    $OutObj = Out::init();
    $OutObj->setLogFunc(function($ret){
        \XEz::log('/proxy_guess/test.log',$ret,'json');
    });
    $OutObj->setVpnMssIpv4Arr($vpn_mss_ipv4_arr);
    $OutObj->setGlobal($global);


    if($ret_arr = $global->watch('proxy_guess_data:'.$_GET['token'], 2)){

        if(empty($_GET['_d'])){
            if( $ret_arr['0']['port'] == $ret_arr['1']['port']){
                $t_302_arr = $global->watch('proxy_guess_data:'.$ret_arr['0']['ip'].':'.$ret_arr['0']['port'], 2);
                $ret[] = $OutObj->out($ret_arr['0']['ip'],$ret_arr['0']['port'],$t_302_arr,isset($ret_arr['ws'])?$ret_arr['ws']:false);

                $ret2 = [
                        'guess use proxy'=>$OutObj->proxy_guess_info['guess use proxy'],
                        'guess use proxy socre'=>$OutObj->proxy_guess_info['guess use proxy socre'],
                        'ip'=>$ret_arr['0']['ip'],
                        'port'=>$ret_arr['0']['port'],
                        'vpn by mss'=>$OutObj->fp['vpn_detected'],
                        'tcp mss'=>$OutObj->fp['tcp_mss'],
                        'you_to_proxy'=>$OutObj->proxy_guess_info['you_to_proxy'],
                        'proxy_to_server'=>$OutObj->proxy_guess_info['proxy_to_server'],
                        'fp'=>$OutObj->fp,
                        'ja3'=>$OutObj->ja3,

                ];
                isset($OutObj->proxy_guess_info['302_rrt']) && $ret2['302_rrt'] = round($OutObj->proxy_guess_info['302_rrt'],3);
                isset($OutObj->proxy_guess_info['guess use proxy by 302_rrt']) && $ret2['guess use proxy by 302_rrt'] = $OutObj->proxy_guess_info['guess use proxy by 302_rrt'];


                isset($OutObj->proxy_guess_info['ws']) && $ret2['ws'] = $OutObj->proxy_guess_info['ws'];
                isset($OutObj->proxy_guess_info['ws_rrt']) && $ret2['ws_rrt'] = round($OutObj->proxy_guess_info['ws_rrt'],3);
                isset($OutObj->proxy_guess_info['guess use proxy by ws_rrt']) && $ret2['guess use proxy by ws_rrt'] = $OutObj->proxy_guess_info['guess use proxy by ws_rrt'];



            }else{
                $ret[] = $OutObj->out($ret_arr['0']['ip'],$ret_arr['0']['port'],[],$ret_arr['ws']);
                $ret[] = $OutObj->out($ret_arr['1']['ip'],$ret_arr['1']['port'],[],$ret_arr['ws']);
                $ret2 = [
                    'guess use proxy'=>min($ret[0]['fp']['uptime_interpolation']['guess use proxy'],$ret[1]['fp']['uptime_interpolation']['guess use proxy']),
                    'guess use proxy socre'=>$ret[0]['fp']['uptime_interpolation']['guess use proxy']>$ret[1]['fp']['uptime_interpolation']['guess use proxy']?
                        $ret[0]['fp']['uptime_interpolation']['guess use proxy socre']:$ret[1]['fp']['uptime_interpolation']['guess use proxy socre'],
                    'ip'=>$ret_arr['0']['ip'],
                    'port'=>$ret_arr['0']['port'],
                    'vpn by mss'=>$ret[0]['vpn_detected'],
                    'tcp mss'=>$ret[0]['tcp_mss'],
                    'you_to_proxy'=>min($ret['0']['you_to_proxy'],$ret['1']['you_to_proxy']),
                    'proxy_to_server'=>min($ret['0']['proxy_to_server'],$ret['1']['proxy_to_server']),
                    'fp'=>$OutObj->fp,
                    'ja3'=>$OutObj->ja3,
                ];
                isset($ret['0']['302_rrt']) && $ret2['302_rrt'] = round($ret['0']['302_rrt'],3);
                isset($ret['0']['guess use proxy by 302_rrt']) && $ret2['guess use proxy by 302_rrt'] = $ret['0']['guess use proxy by 302_rrt'];


                isset($ret['0']['ws']) && $ret2['ws'] = $ret['0']['ws'];
                isset($ret['0']['ws_rrt']) && $ret2['ws_rrt'] = round($ret['0']['ws_rrt'],3);
                isset($ret['0']['guess use proxy by ws_rrt']) && $ret2['guess use proxy by ws_rrt'] = $ret['0']['guess use proxy by ws_rrt'];

            }

            echo '<pre>';
            echo json_encode($ret2,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
            echo '</pre>';
        }else{
            if( $ret_arr['0']['port'] == $ret_arr['1']['port']){
                $t_302_arr = $global->watch('proxy_guess_data:'.$ret_arr['0']['ip'].':'.$ret_arr['0']['port'], 2);
                $ret[] = $OutObj->out($ret_arr['0']['ip'],$ret_arr['0']['port'],$t_302_arr);
            }else{
                $ret[] = $OutObj->out($ret_arr['0']['ip'],$ret_arr['0']['port'],[]);
                $ret[] = $OutObj->out($ret_arr['1']['ip'],$ret_arr['1']['port'],[]);
            }

            echo '<pre>';
            echo json_encode($ret,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
            echo '</pre>';
        }


    }

    exit;
}

?>

<html>
<head></head>
<body>
<h3>by_302_img <a id="by_302_img_a" href="javascript:return;">done</a></h3>
<div id="by_302_img">

</div>
<h3>by_ws_without_message  <a id="by_ws_without_message_a" href="javascript:withoutMessage();">refresh</a></h3>
<div id="by_ws_without_message">

</div>
<h3>by_ws_with_message  <a id="by_ws_with_message_a"  href="javascript:withMessage();">refresh</a></h3>
<div id="by_ws_with_message">

</div>
<script>



    setTimeout(function(){
        by_302_img()
        withMessage()
        withoutMessage()
    },500)

    function by_302_img(){
        document.getElementById('by_302_img_a').innerText = 'doing';
        var image = new Image();
        var token = Math.random();
        image.onload = function(){
            setTimeout(function(){
              if(typeof fetch =="function"){
                fetch('index.php?token='+token)
                  .then(function(response){return response.text()})
                  .then(function(data){document.getElementById('by_302_img').innerHTML = data;document.getElementById('by_302_img_a').innerText = 'done'});
              }else{
                var image2 = new Image();
                image.onerror = function(){
                  document.getElementById('by_302_img').innerHTML = '<iframe src="'+'index.php?token='+token+'" width="500" height="500"></iframe>';
                  document.getElementById('by_302_img_a').innerText = 'done'
                }
                image2.src = 'index.php?token='+token
              }

            },1000);
        }
        image.src = "proxy_guess.php?token="+token;
    }
    function withoutMessage(){
        document.getElementById('by_ws_without_message_a').innerText = 'doing';
        var socket;
        var token = Math.random();
        socket = new WebSocket('wss://'+window.location.host+'/wss?token='+token);
        socket.onopen = function () {
            socket.close()
            setTimeout(function(){

                fetch('index.php?token='+token)
                    .then((response) => response.text())
                    .then((data) => {
                        document.getElementById('by_ws_without_message').innerHTML = data;
                        document.getElementById('by_ws_without_message_a').innerText = 'refresh'
                    });
            },1000)
        }
    }

    function withMessage(){
        document.getElementById('by_ws_with_message_a').innerText = 'doing';
        var socket;
        var token = Math.random();
        socket = new WebSocket('wss://'+window.location.host+'/wss?token='+token);
        socket.onopen = function () {

        }
        var c= 0;
        socket.onmessage = function (event) {
            c++;
            socket.send(event.data);
            if (c > 2) {
                socket.close()
                setTimeout(function(){

                    fetch('index.php?token='+token)
                        .then((response) => response.text())
                        .then((data) => {
                            document.getElementById('by_ws_with_message').innerHTML = data;
                            document.getElementById('by_ws_with_message_a').innerText = 'refresh'
                        });
                },1000)
            }
        }
    }


</script>
</body>
</html>
