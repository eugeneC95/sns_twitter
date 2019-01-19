<?php
include("head.php");include("config.php");?>
<div id='container'>
<?php
$conn = new PDO("mysql:host=$servername;dbname=$db;charset=UTF8", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$id = htmlspecialchars($_GET[i]);
if($id == ""){
  $id = htmlspecialchars($_COOKIE['user_name']);
}
//show profile page
try {
  $sth = $conn->prepare("SELECT * FROM user WHERE user_name = '$id'");
  $sth->execute();
  $datas = $sth->fetchAll();
  foreach($datas as $mydata){
    if($mydata[description] == ''){$desc = "Not Set";}else{$desc = $mydata[description];}
    if($id != htmlspecialchars($_COOKIE['user_name'])){
      $function = "disabled";
      $hidden = "hidden";
      $myid = htmlspecialchars($_COOKIE['user_name']);
      try {
        $sth = $conn->prepare("SELECT * FROM follow_flow WHERE user = '$myid' && follow = '$id'");
        $sth->execute();
        $datas = $sth->fetchAll();
        if(count($datas) < 1){
          $follow= "<form method='POST' action='/sns/loading.php'><input type='text' value='".$id."' name='follow' hidden><button type='submit' name='f_btn' class='btn btn-outline-warning'>Follow</button></form>";
        }else{
          $follow= "<form method='POST' action='/sns/loading.php'><input type='text' value='".$id."' name='follow' hidden><button type='submit' name='unf_btn' class='btn btn-outline-warning'>UnFollow</button></form>";
        }
      }
      catch(PDOException $e){
        echo "Connection failed: " . $e->getMessage();$conn=null;}
    }
    echo "
    <div class='col-auto mx-auto py-5 row'>
      <div class='border rounded py-3 col-sm-4'>
        <div class='clearfix'>
          <div class='float-left'>Account</div>
          <div class='float-right'>".$follow."</div>
        </div>
        <div class='my-2'>
          <img src=".$mydata[img]." width=100 height=100>
        </div>
        <form method ='POST' action='/sns/loading.php' enctype='multipart/form-data'>
          <input type='text' name='p_id' value=".$id." hidden>
          <input type='file' class='form-group btn btn-sm btn-outline-secondary' name='p_img' ".$hidden." accept='image/*'>
          <button class='btn btn-warning mb-3' name='p_img_btn' ".$hidden." ".$function.">Upload</button>
        </form>
        <form method='POST' action='/sns/loading.php'>
          <h6>Id: <input type='text' class='form-control col-10' value=".$mydata[user_name]." pattern='^[A-Za-z0-9_]{1,15}$' disabled></h6>
          <h6>Display: <input type='text' class='form-control col-10' name='p_showname' ".$function." value='".$mydata[show_name]."' pattern='^[A-Za-z0-9_]{1,15}$' required></h6>
          <h6>Email: <input type='email' class='form-control col-10' name='p_email' ".$function." value=".$mydata[email]." required></h6>
          <h6>Description: <textarea class='form-control col-10' name='p_desc' ".$function." required>".$desc."</textarea></h6>
          <button type='submit' name='p_update' class='btn btn-success' ".$function." ".$hidden.">Update</button>
        </form>
        <a href='/sns/logout.php' class='float-right'><button class='btn btn-danger' ".$hidden.">Log Out</button></a>
      </div>
      <div class='border rounded py-3 ml-1 col-sm-7'>";
    }
        //profile tl
        try {
          $sth = $conn->prepare("SELECT post.id as ids,post.*,user.*,(SELECT COUNT(*) FROM like_flow WHERE like_flow.like_post_id = post.id) as likes FROM post LEFT JOIN user ON post.user_name = user.user_name WHERE post.user_name = '$id' ORDER BY post.created_time DESC");
          $sth->execute();
          $datas = $sth->fetchAll();
          foreach ($datas as $data) {
            if($data[imgs] != ""){
                $imgs = "<div><img src='".$data[imgs]."' class='img-fluid col-6'></div>";
            }else{$imgs='';}
            $sth = $conn->prepare("SELECT like_name FROM like_flow WHERE like_post_id = '$data[ids]' || like_name = '$data[ids]'");
            $sth->execute();
            $like_names = $sth->fetchAll();
            if(count($like_names) >= 1){
              $like_btn = "<button type='submit' class='thumb_down' name='unlike_btn'><i class='material-icons'>thumb_up</i></button>";
            }else{
              $like_btn = "<button type='submit' class='thumb_up' name='like_btn'><i class='material-icons'>thumb_up</i></button>";
            }
            echo "
            <img src=".$data[img]." width='40' height='40' class='float-left mb-1'>
            <div class='ml-5 pl-auto'>
              <div>".$data[show_name]."@<a href='/sns/profile.php?i=".$data[user_name]."'>".$data[user_name]."</a></div>
              <div>".$data[body]."</div>
              ".$imgs."
              <div>
                <form method='post' action='/sns/loading.php'>
                  <input type='text' name='like_post' value='".$data[ids]."' hidden>
                  ".$like_btn."
                  <span>".$data[likes]."</span>
                </form>
              </div>
            </div>
            <div style='clear:both!important;'></div>
            ";
          }
      }
      catch(PDOException $e){
        echo "Connection failed: " . $e->getMessage();$conn=null;}
      echo "
      </div>
    </div>
    ";
}
catch(PDOException $e){
  echo "Connection failed: " . $e->getMessage();$conn=null;}?>
</div>
<?php include("foot.php");?>
