<?php


if (empty($_GET['image'])) {
    if (empty($_GET['t'])) {

        header('Location: '.$_SERVER['REQUEST_URI'].''.(empty($_SERVER['QUERY_STRING']) ? '?' : '&').'t='
            .$_SERVER['REQUEST_TIME_FLOAT']);

        exit;
    } else {
        $t = round($_SERVER['REQUEST_TIME_FLOAT'] - $_GET['t'], 3);
    }
} else {

    if (empty($_GET['t'])) {
        header('Location: '.$_SERVER['REQUEST_URI'].''.(empty($_SERVER['QUERY_STRING']) ? '?' : '&').'t='
            .$_SERVER['REQUEST_TIME_FLOAT']);
        exit;
    } else {
        if(empty($_GET['js'])){
            header("Content-Type: image/png");
            $t = round($_SERVER['REQUEST_TIME_FLOAT'] - $_GET['t'], 3);
            $t = $t * 1000;
            header('t:'.$t);
            $im = @imagecreate(1, $t) or die("Cannot Initialize new GD image stream");
            $background_color = imagecolorallocate($im, 0, 0, 0);
            imagejpeg($im, null, 0);
            imagedestroy($im);
            exit;
        }else{
            echo  $t = round($_SERVER['REQUEST_TIME_FLOAT'] - $_GET['t'], 3);
            exit;
        }

    }

}


?>

<!DOCTYPE html>
<html lang="">
<head>
<style>
    table td{
        padding: 0 5px;
    }
</style>
</head>
<body>
<table id="table">
    <thead>
    <tr>
        <th>name</th>
        <th>rtt(ms)</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>302</td>
        <td> <?php echo $t; ?> </td>
    </tr>

    </tbody>
</table>

<script type="application/javascript">
  history.replaceState({}, '', window.location.origin + window.location.pathname);
  function load_image(name, t, fetchPriority){
    let fun = function () {
      let img = new Image()
      img.id = name
      if(fetchPriority) img.fetchPriority = fetchPriority;
      img.onload = function () {
        console.log(name, img.height / 1000)

        document.getElementsByTagName('tbody')[0].insertAdjacentHTML('beforeend',
          '<tr> <td>'+name+'</td> <td><span id="'+name+'">'+ ((img.height) / 1000).toFixed(3)+'</span></td> </tr>'
        )
      }
      img.src = 'index.php?image='+name
    };
    if(t>=0){
      setTimeout(fun,t)
    }else{
      fun()
    }

  }

  function _fetch(name, t){
    let fun = function () {
      fetch('index.php?image='+name+'&js=1').then((r)=>r.text()).then(function(r){
        console.log(name, r)
        document.getElementsByTagName('tbody')[0].insertAdjacentHTML('beforeend',
          '<tr> <td>'+name+'</td> <td><span id="'+name+'">'+ (r*1).toFixed(3)+'</span></td> </tr>'
        )
      })

    };
    if(t>=0){
      setTimeout(fun,t)
    }else{
      fun()
    }
  }

  window.onload = function () {
   
    _fetch('fetch_after_window_load_0sm',0,'high')
    _fetch('fetch_after_window_load_100sm',100,'high')
    _fetch('fetch_after_window_load_200sm',200,'high')
    _fetch('fetch_after_window_load_300sm',300,'high')
    _fetch('fetch_after_window_load_400sm',400,'high')
    _fetch('fetch_after_window_load_500sm',500,'high')
    _fetch('fetch_after_window_load_600sm',600,'high')
    _fetch('fetch_after_window_load_700sm',700,'high')
    _fetch('fetch_after_window_load_800sm',800,'high')
    _fetch('fetch_after_window_load_900sm',900,'high')

  }


</script>
<div style="display:none">
</div>
</body>
</html>

