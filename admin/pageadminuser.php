  <div class="container collapse notransition" id="adminuser" data-bs-parent="#actions">
    <p>Welcome, <?=$_SESSION['firstname']?>!</p>
    <p>Add Admin Users</p>
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
    <p>Modify Admin Users</p>
    <table class="table table-striped table-hover">
      <tbody>
<?php
$usersql = 'SELECT user_id, user_first_name, user_last_name, user_email FROM users ORDER BY user_last_name ASC';
$userresult = mysqli_query($link, $usersql);
if (mysqli_num_rows($userresult) > 0) {
  while ($userrow = mysqli_fetch_assoc($userresult)) {
?>
<tr>
  <td><button class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#user<?=$userrow['user_id']?>modal"><?=$userrow['user_last_name']?>,&nbsp;<?=$userrow['user_first_name']?></button></td>
  <td><button type="button" class="link" data-bs-toggle="modal" data-bs-target="#user<?=$userrow['user_id']?>delete"><i class="fa-solid fa-xmark text-danger"></i></button></td>
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
