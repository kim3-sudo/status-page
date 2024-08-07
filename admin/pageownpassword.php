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
  <div class="container collapse notransition" id="updatepassword" data-bs-parent="#actions">
    <h6 class="my-3">Welcome, <?=$_SESSION['firstname']?>!</h6>
    <h1 class="my-3">Change Your Password</h1>
    <form action="updateownpassword.php" method="post">
      <div class="mb-3">
        <label for="oldpassword" class="form-label">Old Password<span class="required">*</span></label>
        <input type="password" class="form-control" id="oldpassword" name="oldpassword" required>
      </div>
      <div class="mb-3">
        <label for="updateownpassword" class="form-label">New Password<span class="required">*</span></label>
        <input type="password" class="form-control" id="updateownpassword" name="updateownpassword" required>
      </div>
      <div class="mb-3">
        <label for="updateownpasswordconfirm" class="form-label">New Password Confirm<span class="required">*</span></label>
        <input type="password" class="form-control" id="updateownpasswordconfirm" name="updateownpasswordconfirm" required>
      </div>
      <button type="submit" class="btn btn-primary">Submit Password Change</button>
    </form>
    <hr class="my-3">
    <form action="verifytotpenrollment.php" method="post" class="mt-3">
      <div class="mb-3">
        <h1 class="my-3">Time-Based One Time Passcode 2FA</h1>
<?php
$sql = "SELECT user_totpenabled FROM users WHERE user_id = " . $_SESSION['id'];
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$isenrolled = $row['user_totpenabled'];
if ($isenrolled == 1) {
?>
        <input type="radio" name="totpenabled" value="1" id="totptrue" checked>
        <label for="totptrue">TOTP Enabled</label>
        <br>
        <input type="radio" name="totpenabled" value="0" id="totpfalse">
        <label for="totpfalse">TOTP Disabled</label>
<?php
} else {
?>
        <input type="radio" name="totpenabled" value="1" id="totptrue">
        <label for="totptrue">TOTP Enabled</label>
        <br>
        <input type="radio" name="totpenabled" value="0" id="totpfalse" checked>
        <label for="totpfalse">TOTP Disabled</label>
<?php
}
?>
      </div>
      <button type="submit" class="btn btn-primary">Submit TOTP 2FA Settings</button>
      <p>If you need a new client secret, disable, then reenable TOTP 2FA.</p>
    </form>
  </div>
