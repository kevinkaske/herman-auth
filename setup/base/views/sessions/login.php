<form class="form account-form" method="POST" action="<?= getPath('sessions/process'); ?>">
  <div class="form-group">
    <label for="login-username" class="placeholder-hidden">Email</label>
    <input type="text" class="form-control" id="login-username" placeholder="Email" name="email" tabindex="1">
  </div> <!-- /.form-group -->
  <div class="form-group">
    <label for="login-password" class="placeholder-hidden">Password</label>
    <input type="password" class="form-control" id="login-password" placeholder="Password" name="password" tabindex="2">
  </div> <!-- /.form-group -->
  <div class="form-group clearfix">
    <div class="pull-left">
      <label class="checkbox-inline">
        <input type="checkbox" name="remember" tabindex="3">Remember me
      </label>
    </div>
    <div class="pull-right">
      <a href="<?= getPath('sessions/forgot_password'); ?>">Forgot Password?</a>
    </div>
  </div> <!-- /.form-group -->
  <div class="form-group">
    <button type="submit" class="btn btn-primary btn-block btn-lg" tabindex="4">
      Signin &nbsp; <i class="fa fa-play-circle"></i>
    </button>
  </div> <!-- /.form-group -->
</form>
