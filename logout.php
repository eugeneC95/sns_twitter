<?php
$memcache = new Memcached();
$memcache->addServer('localhost', 11211);
setcookie("user_name", "", time()-3600,'/sns');
setcookie("sns_status", "", time()-3600,'/sns');
setcookie("err", "", time()-3600,'/sns');
$memcache->set('content', 'Logged out,byebye', $timeout);
$memcache->set('css', 'success', $timeout);
header("refresh:0;url=https://eugenes.club/sns/login.php");
?>
