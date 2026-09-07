<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Permission is hereby granted, free of charge, to any person obtaining a copy of 
  this software and associated documentation files (the “Software”), to deal in 
  the Software without restriction, including without limitation the rights to 
  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies 
  of the Software, and to permit persons to whom the Software is furnished to do 
  so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in all 
  copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
  SOFTWARE.
*/
?>
<?php require('_guard.php'); ?>
  <div class="container collapse notransition" id="adminuser" data-bs-parent="#actions">
    <h6 class="my-3">Welcome, <?=$_SESSION['firstname']?>!</h6>
    <h1 class="my-3">Admin Users</h1>
    <h2 class="my-3">Add Users</h2>
    <form action="adduser.php" method="post">
      <div class="mb-3">
        <label for="adduserlast" class="form-label">User Last<span class="required">*</span></label>
        <input type="text" class="form-control" id="adduserlast" name="adduserlast" maxlength="64" required>
      </div>
      <div class="mb-3">
        <label for="adduserfirst" class="form-label">User First<span class="required">*</span></label>
        <input type="text" class="form-control" id="adduserfirst" name="adduserfirst" maxlength="64" required>
      </div>
      <div class="mb-3">
        <label for="adduseremail" class="form-label">User Email<span class="required">*</span></label>
        <input type="email" class="form-control" id="adduseremail" name="adduseremail" maxlength="100" required>
      </div>
      <div class="mb-3">
        <p class="small">A temporary password will be generated when the account is created. The user should change this password on next login.</p>
      </div>
      <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <hr class="mt-3 mb-3">
    <h2 class="my-3">Modify Admin Users</h2>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>User</th>
          <th>2FA Enabled</th>
          <th>Delete</th>
        </tr>
      </thead>
      <tbody>
<?php
$usersql = 'SELECT user_id, user_first_name, user_last_name, user_email, user_totpenabled FROM users ORDER BY user_last_name ASC';
$userresult = mysqli_query($link, $usersql);
if (mysqli_num_rows($userresult) > 0) {
  while ($userrow = mysqli_fetch_assoc($userresult)) {
?>
<tr>
  <td><button class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#user<?=$userrow['user_id']?>modal"><?=$userrow['user_last_name']?>,&nbsp;<?=$userrow['user_first_name']?></button></td>
  <td>
<?php
    if ($userrow['user_totpenabled'] == 1) {
      echo 'Yes';
    } else {
      echo 'No';
    }
?>
  </td>
  <td><button type="button" class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#user<?=$userrow['user_id']?>delete"><i class="fa-solid fa-xmark text-danger"></i></button></td>
</tr>
<?php
  }
} else {
  echo '<tr><td>No users fetched</td></tr>';
}
?>
      </tbody>
    </table>
  </div>
