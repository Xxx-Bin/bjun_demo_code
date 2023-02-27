<?php
##  see https://bjun.tech/blog/xphp/210

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
            //
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

    <tr>
        <td>img_in_dom</td>
        <td><span id="img_in_dom"></span></td>
    </tr>

    <tr>
        <td> js_after_dom_load_high_pri_0sm</td>
        <td><span id="js_after_dom_load_high_pri_0sm"></span></td>
    </tr>
    <tr>
        <td>js_in_dom</td>
        <td><span id="js_in_dom"></span></td>
    </tr>
    <tr>
        <td> img_after_js_load_high_pri_0sm</td>
        <td><span id="img_after_js_load_high_pri_0sm"></span></td>
    </tr>
    <tr>
        <td>img_window_onload</td>
        <td><span id="img_window_onload"></span></td>
    </tr>
    <tr>
        <td> img_after_window_load_high_pri_0sm</td>
        <td><span id="img_after_window_load_high_pri_0sm"></span></td>
    </tr>

    <tr>
        <td>js_after_window_load_0sm</td>
        <td><span id="js_after_window_load_0sm"></span></td>
    </tr>
    <tr>
        <td> img_after_dom_load_1s</td>
        <td><span id="img_after_dom_load_1s"></span></td>
    </tr>
    <tr>
        <td>img_after_dom_load_high_pri_1s</td>
        <td><span id="img_after_dom_load_high_pri_1s"></span></td>
    </tr>

    </tbody>
</table>

<script type="application/javascript">
  history.replaceState({}, '', window.location.origin + window.location.pathname)

  function image_onload() {
    let img = document.getElementById('id_img_in_dom')
    console.log('img_in_dom', img.height / 1000)
    document.getElementById('img_in_dom').innerText = ((img.height) / 1000).toFixed(3)
  }
  fetch('index.php?image=js_in_dom&js=1').then((r)=>r.text()).then(function(r){
    console.log('js_in_dom', r)
    document.getElementById('js_in_dom').innerText = (r*1).toFixed(3)
  })

  setTimeout(function () {
    let img = new Image()
    img.id = 'img_after_dom_load_high_pri_0sm'
    img.fetchPriority = 'high';
    img.onload = function () {
      console.log('img_after_dom_load_high_pri_0sm', img.height / 1000)

      document.getElementById('img_after_js_load_high_pri_0sm').innerText = ((img.height) / 1000).toFixed(3)
    }
    img.src = 'index.php?image=img_after_js_load_high_pri_0sm'

    fetch('index.php?image=js_after_dom_load_high_pri_0sm&js=1').then((r)=>r.text()).then(function(r){
      console.log('js_after_dom_load_high_pri_0sm', r)
      document.getElementById('js_after_dom_load_high_pri_0sm').innerText = (r*1).toFixed(3)
    })
  }, 0)


  setTimeout(function () {
    let img = new Image()
    img.id = 'img_after_dom_load'
    img.onload = function () {
      console.log('img_after_dom_load_1s', img.height / 1000)

      document.getElementById('img_after_dom_load_1s').innerText = ((img.height) / 1000).toFixed(3)
    }
    img.src = 'index.php?image=img_after_dom_load_1s'

  }, 1000)

  setTimeout(function () {
    let img = new Image()
    img.id = 'img_after_dom_load_high_pri_1s'
    img.fetchPriority = 'high';
    img.onload = function () {
      // let img = document.getElementById('img_after_dom_load_high_pri')
      console.log('img_after_dom_load_high_pri_1s', img.height / 1000)

      document.getElementById('img_after_dom_load_high_pri_1s').innerText = ((img.height) / 1000).toFixed(3)
    }
    img.src = 'index.php?image=img_after_dom_load_high_pri_1s'

  }, 1000)

  window.onload = function () {
    let img = new Image()
    img.id = 'img_window_onload'
    img.fetchPriority = 'high';
    img.onload = function () {
      // let img = document.getElementById('img_after_dom_load_high_pri')
      console.log('img_window_onload', img.height / 1000)

      document.getElementById('img_window_onload').innerText = ((img.height) / 1000).toFixed(3)
    }
    img.src = 'index.php?image=img_window_onload'

    // 页面加载完毕

    setTimeout(function () {
      let img = new Image()
      img.id = 'img_after_window_load_high_pri_0sm'
      img.fetchPriority = 'high';
      img.onload = function () {
        // let img = document.getElementById('img_after_dom_load_high_pri')
        console.log('img_after_window_load_high_pri_0sm', img.height / 1000)

        document.getElementById('img_after_window_load_high_pri_0sm').innerText = ((img.height) / 1000).toFixed(3)
      }
      img.src = 'index.php?image=img_after_window_load_high_pri_0sm'

    }, 0)

    setTimeout(function () {
      fetch('index.php?image=js_after_window_load_0sm&js=1').then((r)=>r.text()).then(function(r){
        console.log('js_after_window_load_0sm', r)
        document.getElementById('js_after_window_load_0sm').innerText = (r*1).toFixed(3)
      })

    }, 0)
  }

</script>
<div style="display:none">
    <img id="id_img_in_dom" src="index.php?image=img_in_dom" onload="image_onload()">
</div>
</body>
</html>

