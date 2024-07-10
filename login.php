<?php
/*
    Status Page
    Copyright (C) 2024 Sejin Kim

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
?>
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
