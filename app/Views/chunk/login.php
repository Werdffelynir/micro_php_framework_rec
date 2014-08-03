
<div class="login-form">
  <form id="form" name="form" action="<?=Rec::$url?>login" method="post" autocomplete="off" >
    <div id="block">

      <label id="user" for="username">Username</label>
      <input type="text" name="username" id="name" placeholder="Username" required/>

      <label id="pass" for="password">Password</label>
      <input type="password" name="password" id="password" placeholder="Password" required />

      <input type="submit" id="submit" name="submit" value="Auth"/>
    </div>
  </form>
</div>