<!doctype html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,user-scalable=yes">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href='/sns/css/main.css' type='text/css'>
  <?php
  $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  if($_COOKIE[sns_status] != "logged" && $_COOKIE[user_name] == ""){
    if(strpos($url,'/login.php') == false){
      header("refresh:0;url=https://eugenes.club/sns/login.php");
    }
  }
  ?>
</head>
<body>
<div id="top_bar">
  <ul>
    <li><a id="logo" href="/sns/"><img src="/h/img/logo.png"></a></li>
    <li class="right"><a href="/sns/profile.php"><?php echo $_COOKIE[user_name];?></a></li>
  </ul>
</div>
<?php
$memcache = new Memcached();
$memcache->addServer('localhost', 11211);
$css_id = $memcache->get('css');
if($css_id == ''){
  $css_id = 'normal';
}
echo "<div id='".$css_id."' class='mx-auto'>";
  $content = $memcache->get('content');
  if($content != ''){
    echo $content;
  }
?></div>
