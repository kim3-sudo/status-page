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
