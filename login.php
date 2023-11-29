<?php
session_start();
if (isset($_SESSION['loggedin'])) {
  header('Location: admin.php');
}
include('templates/_header.php');
?>
<div class="text-center form-signin w-100 m-auto">
  <form action="authenticate.php" method="post">
    <h1 class="h3 mb-3 fw-normal">Please sign in</h1>
    <div class="form-floating">
      <input type="email" class="form-control" id="email" name="email" placeholder="smith10@organization.tld" required>
      <label for="email">Email address</label>
    </div>
    <div class="form-floating">
      <input type="password" class="form-control" id="password" name="password" placeholder="password" required>
      <label for="password">Password</label>
    </div>
    <button class="w-100 btn btn-lg btn-primary" type="submit">Sign in</button>
  </form>
<?php
$srow = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'enable_sso'"));
if ($srow['setting_value'] == 'true') {
?>
  <br>
  <a class="w-100 btn btn-lg btn-success" href="saml">Sign in with SSO</a>
<?php
}
?>
</div>
<?php
include('templates/_footer.php');
?>
