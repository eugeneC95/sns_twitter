<?php
include("head.php");include("config.php");
$id = htmlspecialchars($_COOKIE[user_name]);
$conn = new PDO("mysql:host=$servername;dbname=$db;charset=UTF8", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
  $sth = $conn->prepare("SELECT * FROM user WHERE user_name = '$id'");
  $sth->execute();
  $datas = $sth->fetchAll();
  foreach ($datas as $data) {
    $img= $data[img];
    $showname = $data[show_name];
  }
}
catch(PDOException $e){
  echo "Connection failed: " . $e->getMessage();$conn=null;
}
?>
<div id='container' class='py-5'>
  <div class='border rounded col-sm-8 mx-auto pt-3 pb-3'>
    <form method="POST" action="loading.php" class='col-sm-11 mx-auto' enctype="multipart/form-data">
      <img src="<?php echo $img;?>" width="40" height="40" class='mb-1 float-left'>
      <span style="vertical-align:super;" class='pl-1'>Hi <?php echo $showname."@".$id;?></span>
      <button type='submit' class='btn btn-success float-right' name='tl_btn'>Eugene</button>
      <input type='file' name='tl_img' accept='image/*' class='float-right form-group btn btn-sm btn-outline-secondary mx-auto'>
      <textarea class="form-control float-none" placeholder="Whats Happening now." maxlength="140" name='tl_txt' required></textarea>
    </form>

    <div class='col-auto float-none mt-3 mx-auto'>
      <!-- timeline -->
      <?php
      function designpost($id,$img,$imgs,$user_name,$showname,$body,$likes){
        if($imgs != ""){
            $showimgs = "<div><img src='".$imgs."' class='img-fluid col-6'></div>";
        }else{$showimgs='';}
        $sth = $GLOBALS['conn']->prepare("SELECT like_name FROM like_flow WHERE like_post_id = '$id' || like_name = '$id'");
        $sth->execute();
        $like_names = $sth->fetchAll();
        if(count($like_names) >= 1){
          $like_btn = "<button type='submit' class='thumb_down' name='unlike_btn'><i class='material-icons'>thumb_up</i></button>";
        }else{
          $like_btn = "<button type='submit' class='thumb_up' name='like_btn'><i class='material-icons'>thumb_up</i></button>";
        }
        echo "
        <div class='my-3'>
          <img src=".$img." width='40' height='40' class='float-left mb-1'>
          <div class='ml-5 pl-auto'>
            <div>".$show_name."@<a href='/sns/profile.php?i=".$user_name."'>".$user_name."</a></div>
            <div>".$body."</div>
            ".$showimgs."
            <div>
              <form method='post' action='/sns/loading.php'>
                <input type='text' name='like_post' value='".$id."' hidden>
                ".$like_btn."
                <span>".$likes."</span>
              </form>
            </div>
          </div>
        </div>
        <div style='clear:both!important;'></div>";
      }
      $id = htmlspecialchars($_COOKIE[user_name]);
      try {
        $sth = $conn->prepare("SELECT post.id as id,post.body as body,post.imgs,user.show_name,user.user_name,user.img,(SELECT COUNT(*) FROM like_flow WHERE like_flow.like_post_id = post.id) as likes FROM post LEFT JOIN follow_flow ON follow_flow.follow = post.user_name LEFT JOIN user ON post.user_name = user.user_name
        WHERE follow_flow.user = '$id' || post.user_name = '$id' ORDER BY post.created_time DESC");
        $sth->execute();
        $datas = $sth->fetchAll();
        $s =0;
        foreach ($datas as $data) {
          if($s%3==0){
            $sth = $conn->prepare("SELECT post.id as id,post.body as body,post.imgs,user.show_name,user.user_name,user.img,(SELECT COUNT(*) FROM like_flow WHERE like_flow.like_post_id = post.id) as likes FROM post LEFT JOIN follow_flow ON follow_flow.follow = post.user_name LEFT JOIN user ON post.user_name = user.user_name
            WHERE follow_flow.user != '$id' || post.user_name != '$id' ORDER BY RAND() LIMIT 0,1");
            $sth->execute();
            $suggestdatas = $sth->fetchAll();
            foreach($suggestdatas as $data1){
               designpost($data1[id],$data1[img],$data1[imgs],$data1[user_name],$data1[show_name],$data1[body],$data1[likes]);
            }
          }
          designpost($data[id],$data[img],$data[imgs],$data[user_name],$data[show_name],$data[body],$data[likes]);
          $s++;
        }

      }
      catch(PDOException $e){
        echo "Connection failed: " . $e->getMessage();$conn=null;}
      ?>
    </div>
  </div>
</div>
<?php include("foot.php");?>
