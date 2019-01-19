<?php
include("config.php");
$timenow = date("Y-m-d H:i:s");
$timeout = '5';
$conn = new PDO("mysql:host=$servername;dbname=$db;charset=UTF8", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$memcache = new Memcached();
$memcache->addServer('localhost', 11211);
if (isset($_POST[l_btn])) {
  $l_n = htmlspecialchars($_POST[l_n]);
  $l_p = md5(htmlspecialchars($_POST[l_p]));
  try {
    $sth = $conn->prepare("SELECT * FROM user WHERE user_name = '$l_n'");
    $sth->execute();
    $users = $sth->fetchAll();
    if(count($users) < 1){
      $memcache->set('content', 'Id/Password are not matched', $timeout);
      $memcache->set('css', 'warning', $timeout);
      header('refresh:0;url=https://eugenes.club/sns/login.php');
    }else{
      foreach ($users as $user) {
        if($l_n != $user[user_name]){
          $memcache->set('content', 'Username incorrect', $timeout);
          $memcache->set('css', 'warning', $timeout);
        }elseif($l_n == $user[user_name]){
          if($user['password'] == $l_p){
            $sth = $conn->prepare("UPDATE user SET updated_time = '$timenow' WHERE user_name LIKE '$l_n'");
            $sth->execute();
            $memcache->set('content', 'Welcome', $timeout);
            $memcache->set('css', 'success', $timeout);
            setcookie("user_name", $l_n, time()+7*24*60*60,'/sns');
            setcookie("sns_status", "logged", time()+7*24*60*60,'/sns');
            header('refresh:0;url=https://eugenes.club/sns/');
          }elseif($user['password'] != $l_p){
            $memcache->set('content', 'Id/Pasword are not mached', $timeout);
            $memcache->set('css', 'warning', $timeout);
            header('refresh:0;url=https://eugenes.club/sns/login.php');
          }
        }
      }
    }
  } catch (PDOException $e) {
    echo "Connection failed: ".$e->getMessage();
    $conn=null;
  }
}elseif (isset($_POST[r_btn])) {
  $r_n = htmlspecialchars($_POST[r_n]);
  $r_e = htmlspecialchars($_POST[r_e]);
  $r_p = md5($_POST[r_p]);
  $r_d = date("Y-m-d H:i:s");
  $rand =time()*2 .'_'.((time()*3) -95). '_' . rand(001, 999);
  $link = "https://eugenes.club/sns/loading.php?k=".$rand."&e=".$r_e;
  try {
    $conn = new PDO("mysql:host=$servername;dbname=$db;charset=UTF8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sth = $conn->prepare("SELECT * FROM user WHERE user_name LIKE '$r_n'");
    $sth->execute();
    $users = $sth->fetchAll();
    if(count($users) < 1){
      require '../vendor/autoload.php';
      $email = new \SendGrid\Mail\Mail();
      $email->setFrom("info@eugenes.club", "Eugene");
      $email->setSubject("Hi,From SNS-Eugenes.club");
      $email->addTo($r_e, $r_n);
      $email->addContent("text/html", "Hi,".$r_n."<br>Welcome To Eugenes.Club<br> Please Click the link above to vertificate Your Register.<br><a href='".$link."'>Link</a><br>Its Only available for 24 hour since you register.");
      $sendgrid = new \SendGrid($sendgridapi);
      try {
        $response = $sendgrid->send($email);
        if($response->statusCode() == '202'){
          $memcache->set($r_e, $rand, 86400);
          $sth = $conn->prepare("INSERT INTO user (user_name,email,password,created_time,updated_time)VALUES('$r_n','$r_e','$r_p','$r_d','$r_d')");
          $sth->execute();
          $memcache->set('content', 'Account Registerd', $timeout);
          $memcache->set('css', 'success', $timeout);
          header('refresh:0;url=https://eugenes.club/sns/login.php');
        }else{
          $statuscode = $response->statusCode();
          $statusheader = $response->headers();
          $statusbody = $response->body();
          $sth = $conn->prepare("INSERT INTO error_log (statuscode,headers,body,created_date)VALUES('$statuscode','$statusheader','$statusbody','$r_d')");
          $sth->execute();
          $memcache->set('content', 'Server error,infomation sent to admin', $timeout);
          $memcache->set('css', 'error', $timeout);
          header('refresh:0;url=https://eugenes.club/sns/login.php');
        }
      }catch (Exception $e) {
          echo 'Caught exception: '. $e->getMessage() ."\n";
      }
    }else{
      $memcache->set('content', 'Username has been used', $timeout);
      $memcache->set('css', 'warning', $timeout);
      header('refresh:0;url=https://eugenes.club/sns/login.php');
    }
  }catch(PDOException $e){
    echo "Connection failed: ".$e->getMessage();
    $conn=null;
  }
}elseif(isset($_POST['forgot_btn'])){
  $forgot_n = htmlspecialchars($_POST['forgot_n']);
  $forgot_e = htmlspecialchars($_POST['forgot_e']);
  try {
    $sth = $conn->prepare("SELECT * FROM user WHERE user_name = '$forgot_n' AND email = '$forgot_e'");
    $sth->execute();
    $users = $sth->fetchAll();
    if(count($users) >= 1){
      $rand =time()*2 .'_'.((time()*3) -95). '_' . rand(0001, 9999);
      $link = "https://eugenes.club/sns/loading.php?reset_code=".$rand."&e=".$forgot_e;
      require '../vendor/autoload.php';
      $email = new \SendGrid\Mail\Mail();
      $email->setFrom("info@eugenes.club", "Eugene");
      $email->setSubject("Password Recovery-SNS.Eugenes.club");
      $email->addTo($forgot_e, $forgot_n);
      $email->addContent("text/html", "Hi ".$forgot_n.",From SNS.Eugenes.Club<br>We have seen that you hope to getting the forgotten password.<br>If you hope so,please head to the link below.<br><a href='".$link."'>Reset Link</a><br><br>Its Only available for 1 hour since this email sent.");
      $sendgrid = new \SendGrid($sendgridapi);
      try {
        $response = $sendgrid->send($email);
        if($response->statusCode() == '202'){
          $memcache->set($forgot_e, $rand, 3600);
          $memcache->set('content', 'Email Sent,Check it Now.', $timeout);
          $memcache->set('css', 'success', $timeout);
          header('Location: /sns/login.php');exit;
        }else{
          $statuscode = $response->statusCode();
          $statusheader = $response->headers();
          $sth = $conn->prepare("INSERT INTO error_log (statuscode,headers,body,created_date)VALUES('$statuscode','','$statusheader','$r_d')");
          $sth->execute();
          $memcache->set('content', 'Sorry,Server error now,Please Try again later', $timeout);
          $memcache->set('css', 'error', $timeout);
          header('Location: /sns/login.php');exit;
        }
      }catch (Exception $e) {
          echo 'Caught exception: '. $e->getMessage() ."\n";
      }
    }else{
      $memcache->set('content', 'Email and Id are not matched', $timeout);
      $memcache->set('css', 'warning', $timeout);
      header("refresh:0;url= https://eugenes.club/sns/login.php");exit;
    }
  }catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();$conn=null;}
}elseif(isset($_GET['reset_code'])){
  $rand =time()*2 .'_'.((time()*3) -95). '_' . rand(0001, 9999);
  $reset = htmlspecialchars($_GET['reset_code']);
  $email = htmlspecialchars($_GET['e']);
  $server_reset = $memcache->get($email);
  if($reset == $server_reset){
    $memcache->set($email, $rand, 3600);
    header("Location: /sns/login.php?reset=t&e=$email&code=$rand");$conn=null;exit;
  }else{
    $error_log = $code."|".$email."|".$server_code;
    $sth = $conn->prepare("INSERT INTO error_log (error,created_date)VALUES('$error_log','$timenow')");
    $sth->execute();
    $memcache->set('content', 'Server Error,already sent to admin', $timeout);
    $memcache->set('css', 'error', $timeout);
    header("Location: /sns/login.php");$conn=null;exit;
  }
}elseif(isset($_POST['reset_btn'])){
  $re_code = htmlspecialchars($_POST['re_code']);
  $re_email = htmlspecialchars($_POST['re_email']);
  $pass = md5(htmlspecialchars($_POST['re_pass']));
  $server_reset = $memcache->get($re_email);
  echo $re_code."<br>";
  echo $re_email."<br>";
  echo $pass."<br>";
  echo $server_reset."<br>";
  if($server_reset == $re_code){
    $sth = $conn->prepare("UPDATE user SET password = '$pass' WHERE email = '$re_email'");
    $sth->execute();
    $memcache->set($re_mail, '', 3600);
    $memcache->set('content', 'Password Updated', $timeout);
    $memcache->set('css', 'success', $timeout);
    header("Location: /sns/login.php");$conn=null;exit;
  }else{
    $error_log = $re_code."|".$re_email;
    $sth = $conn->prepare("INSERT INTO error_log (error,created_date)VALUES('$error_log','$timenow')");
    $sth->execute();
    $memcache->set('content', 'Server Error,already sent to admin', $timeout);
    $memcache->set('css', 'error', $timeout);
    header("Location: /sns/login.php");$conn=null;exit;
  }
}elseif(isset($_GET['k'])){
  $email = htmlspecialchars($_GET[e]);
  $key = htmlspecialchars($_GET[k]);
  $stored = $memcache->get($email);
  if($key == $stored){
    try {
      $sth = $conn->prepare("UPDATE user SET vertification = 'yes' WHERE email = '$email'");
      $sth->execute();
      $memcache->set('content', 'Vertification Done,Log in now', $timeout);
      $memcache->set('css', 'success', $timeout);
      header('refresh:0;url=https://eugenes.club/sns/login.php');
    }catch(PDOException $e){
      echo "Connection failed: ".$e->getMessage();
      $conn=null;
    }
  }else{
    $memcache->set('content', 'Vertification Failed,try again later', $timeout);
    $memcache->set('css', 'error', $timeout);
    header('refresh:0;url=https://eugenes.club/sns/login.php');
  }
}elseif(isset($_FILES[p_img])){
  //upload img
  $id = htmlspecialchars($_POST[p_id]);
  $rand = time() . '_' . rand(100, 999) . '.' . end(explode(".",$_FILES["p_img"]["name"]));
  $imageFileType = strtolower(end(explode('.',$_FILES["p_img"]["name"])));
  if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
      $memcache->set('content', 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.', $timeout);
      $memcache->set('css', 'warning', $timeout);
  }else{
    if ($_FILES['p_img']['size'] > 10000000) {
        $memcache->set('content', 'This file is more than 10MB. Sorry, it has to be less than or equal to 10MB', $timeout);
        $memcache->set('css', 'warning', $timeout);
    }else{
      if (move_uploaded_file($_FILES["p_img"]["tmp_name"], "img/" .$rand)) {
        $img = "/sns/img/".$rand;
        try {
          $conn = new PDO("mysql:host=$servername;dbname=$db;charset=UTF8", $username, $password);
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $sth = $conn->prepare("UPDATE user SET img = '$img' WHERE user_name = '$id'");
          $sth->execute();
          $memcache->set('content', 'Image Post sent', $timeout);
          $memcache->set('css', 'success', $timeout);
          header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
        catch(PDOException $e){
          echo "Connection failed: " . $e->getMessage();$conn=null;}
      }else{
        echo "Sorry,".$_FILES["p_img"]["error"];
        header('Location: ' . $_SERVER['HTTP_REFERER']);
      }
    }
  }
}elseif (isset($_POST[p_update])) {
  $id= htmlspecialchars($_COOKIE[user_name]);
  $showname = htmlspecialchars($_POST[p_showname]);
  $email = htmlspecialchars($_POST[p_email]);
  $description = htmlspecialchars($_POST[p_desc]);
  try {
    $conn = new PDO("mysql:host=$servername;dbname=$db;charset=UTF8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sth = $conn->prepare("UPDATE user SET show_name = '$showname',email = '$email',description = '$description' WHERE user_name = '$id'");
    $sth->execute();
    $memcache->set('content', 'Profile Updated', $timeout);
    $memcache->set('css', 'success', $timeout);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
  }
  catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();$conn=null;}
}elseif (isset($_POST[f_btn])) {
  $id= htmlspecialchars($_COOKIE[user_name]);
  $follow = htmlspecialchars($_POST[follow]);
  $timenow = date("Y-m-d H:i:s");
  try {
    $conn = new PDO("mysql:host=$servername;dbname=$db;charset=UTF8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sth = $conn->prepare("INSERT INTO follow_flow (user,follow,created_time)VALUES('$id','$follow','$timenow')");
    $sth->execute();
    $memcache->set('content', 'User Followed', $timeout);
    $memcache->set('css', 'success', $timeout);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
  }
  catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();$conn=null;}
}elseif (isset($_POST[unf_btn])) {
  $id= htmlspecialchars($_COOKIE[user_name]);
  $follow = htmlspecialchars($_POST[follow]);
  $timenow = date("Y-m-d H:i:s");
  try {
    $conn = new PDO("mysql:host=$servername;dbname=$db;charset=UTF8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sth = $conn->prepare("DELETE FROM follow_flow WHERE user = '$id' && follow = '$follow'");
    $sth->execute();
    $memcache->set('content', 'User unFollowed', $timeout);
    $memcache->set('css', 'success', $timeout);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
  }
  catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();$conn=null;}
}elseif (isset($_POST[like_btn])) {
  $id= htmlspecialchars($_COOKIE[user_name]);
  $post_id= htmlspecialchars($_POST[like_post]);
  $timenow= date("Y-m-d H:i:s");
  try {
    $conn = new PDO("mysql:host=$servername;dbname=$db;charset=UTF8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sth = $conn->prepare("INSERT INTO like_flow (like_name,like_post_id,liked_date)VALUES('$id','$post_id','$timenow')");
    $sth->execute();
    $memcache->set('content', 'Post Liked', $timeout);
    $memcache->set('css', 'success', $timeout);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
  }
  catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();}
  $conn=null;
}elseif (isset($_POST[unlike_btn])) {
  $id= htmlspecialchars($_COOKIE[user_name]);
  $post_id= htmlspecialchars($_POST[like_post]);
  $timenow= date("Y-m-d H:i:s");
  try {
    $conn = new PDO("mysql:host=$servername;dbname=$db;charset=UTF8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sth = $conn->prepare("DELETE FROM like_flow WHERE like_post_id ='$post_id' && like_name ='$id'");
    $sth->execute();
    $memcache->set('content', 'Post unLiked', $timeout);
    $memcache->set('css', 'success', $timeout);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
  }
  catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();$conn=null;}
}elseif (isset($_POST[tl_btn])) {
  //add post to timeline
  $id = htmlspecialchars($_COOKIE[user_name]);
  $text = htmlspecialchars($_POST[tl_txt]);
  $timenow = date("Y-m-d H:i:s");
  $randtime =time();
  $rand ="img/". $randtime . '_' . rand(100, 999) . '.' . end(explode(".",$_FILES["tl_img"]["name"]));
  if(!file_exists($_FILES['tl_img']['tmp_name']) || !is_uploaded_file($_FILES['tl_img']['tmp_name'])) {
    try {
      $conn = new PDO("mysql:host=$servername;dbname=$db;charset=UTF8", $username, $password);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sth = $conn->prepare("INSERT INTO post (body,user_name,created_time)VALUES('$text','$id','$timenow')");
      $sth->execute();
      $memcache->set('content', 'Post Sent', $timeout);
      $memcache->set('css', 'success', $timeout);
      header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    catch(PDOException $e){
      echo "Connection failed: " . $e->getMessage();$conn=null;}
  }else{
    $imageFileType = strtolower(end(explode('.',$_FILES["tl_img"]["name"])));
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        $memcache->set('content', 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.', $timeout);
        $memcache->set('css', 'warning', $timeout);
    }else{
      if ($_FILES['tl_img']['size'] > 10000000) {
          $memcache->set('content', 'This file is more than 10MB. Sorry, it has to be less than or equal to 10MB', $timeout);
          $memcache->set('css', 'warning', $timeout);
      }else{
        if (move_uploaded_file($_FILES["tl_img"]["tmp_name"],$rand)) {
          try {
            $conn = new PDO("mysql:host=$servername;dbname=$db;charset=UTF8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sth = $conn->prepare("INSERT INTO post (body,imgs,user_name,created_time)VALUES('$text','/sns/$rand','$id','$timenow')");
            $sth->execute();
            $memcache->set('content', 'Post Sent', $timeout);
            $memcache->set('css', 'success', $timeout);
            header('Location: ' . $_SERVER['HTTP_REFERER']);
          }
          catch(PDOException $e){
            echo "Connection failed: " . $e->getMessage();$conn=null;}
        }else{
          $memcache->set('content', 'Server Error', $timeout);
          $memcache->set('css', 'error', $timeout);
        }
      }
    }
  }


}else{
  $conn=null;
  $memcache->set('content', "Good boy shouldn't do that", $timeout);
  $memcache->set('css', 'error', $timeout);
  header('refresh:0;url=https://eugenes.club/sns/');
}
?>
