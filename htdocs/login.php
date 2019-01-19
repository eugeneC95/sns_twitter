<?php include("head.php");
if($_COOKIE[user_name] != ""){
  header('refresh:0;url=https://eugenes.club/sns/');
}

?>
<div id='container'>
  <?php
  if($_GET['reset'] == 't'){
    echo "
    <div class='pt-5'>
      <div class='mx-auto pt-4 col-11 col-md-4 border rounded'>
        <form method='POST' class='mx-auto col' action='loading.php'>
          <h4>Password Reset</h4>
          <input type='text' value='".$_GET[code]."' name='re_code' hidden>
          <input type='text' value='".$_GET[e]."' name='re_email' hidden>
          <div class='mt-3'>
            <label class='mb-1 pl-1 font-weight-bold'>Password</label>
            <input type='password' name='re_pass' class='form-control' pattern='^(?=.*[0-9])(?=.*[a-z])\S{8,}$' placeholder='Password' title='At least 8 Character.Combination of lowercase and numbers.No Space allowed' required>
          </div>
          <div class='mt-3 col-auto text-center mb-3'>
            <button type='submit' name='reset_btn' class='btn btn-success'>Send</button>
          </div>
        </form>
      </div>
    </div>
    ";
  }
  ?>
  <div class="form-group row pt-5 mx-auto">
    <form method="POST" class="mx-auto col-12 col-md" action="loading.php">
      <div class="border rounded px-4 mb-5">
        <h4 class="col-auto mt-4 text-center">Register</h4>
        <div class="mt-3">
          <label class="mb-1 pl-1 font-weight-bold">Id</label>
          <input type="text" name="r_n" class="form-control" pattern="^[a-z0-9_]{1,15}$" placeholder="Pick a unique Id" title="Only [a-z number _ ] accept" required >
        </div>
        <div class="mt-3">
          <label class="mb-1 pl-1 font-weight-bold">Email</label>
          <input type="email" name="r_e" class="form-control" placeholder="Email" required="">
        </div>
        <div class="mt-3">
          <label class="mb-1 pl-1 font-weight-bold">Password</label>
          <input type="password" name="r_p" class="form-control" placeholder="Password" pattern="^(?=.*[0-9])(?=.*[a-z])\S{8,}$" title="Must contain at least one lowercase and one number.Only lowercase and numeric accepted" required >
        </div>
        <div class="mt-3 col-auto text-center mb-3">
          <button type="submit" name="r_btn" class="btn btn-success">Register</button>
        </div>
      </div>
    </form>
    <form method="POST" class='mx-auto col-12 col-md' action="loading.php">
      <div class="border rounded px-4 mb-5">
        <h4 class="col-auto mt-4 text-center">Login</h4>
        <div class="mt-3">
          <label class="mb-1 pl-1 font-weight-bold">Id</label>
          <input type="text" name='l_n' class="form-control" pattern="^[a-z0-9_]{1,15}$" placeholder="Id" title="Only [a-z number _ ] accept" required>
        </div>
        <div class="mt-3">
          <label class="mb-1 pl-1 font-weight-bold">Password</label>
          <input type="password" name='l_p' class="form-control" pattern="^(?=.*[0-9])(?=.*[a-z])\S{8,}$" placeholder="Password" title="Must contain at least one lowercase and one number.Only lowercase and numeric accepted" required >
        </div>
        <div class="col-auto my-3 text-center">
          <button type="submit" name="l_btn" class="btn btn-warning">Login</button>
        </div>
      </div>
    </form>
    <form method="POST" class='mx-auto col-12 col-md' action="loading.php">
      <div class="border rounded px-4 mb-4">
        <h4 class='col-auto mt-4 text-center'>Forgotten Password</h4>
        <div class="mt-3">
          <label class="mb-1 pl-1 font-weight-bold">Id</label>
          <input type="text" name='forgot_n' class="form-control" pattern="^[a-z0-9_]{1,15}$" placeholder="Id" title="Only [a-z number _ ] accept" required>
        </div>
        <div class="mt-3">
          <label class="mb-1 pl-1 font-weight-bold">Email</label>
          <input type="email" name="forgot_e" class="form-control" placeholder="Email" required>
        </div>
        <div class="col-auto my-3 text-center">
          <button type="submit" name="forgot_btn" class="btn btn-warning">Send</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php include("foot.php");?>
