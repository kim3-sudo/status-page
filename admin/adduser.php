<?php
session_start();
if (!isset($_SESSION['id'])) {
  header('Location: ../login.php');
}
include('../templates/_header.php');
writeToLog($link, 'Adding a new user by admin', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
writeToLog($link, 'Generating new password', $_SESSION['id']);
$fp = @fopen('words.txt', 'r');
if ($fp) {
  $words = explode("\n", fread($fp, filesize('words.txt')));
}
$autogenpassword = '';
$counter = 0;
while ($counter < 4) {
  $rand_key = array_rand($words, 1);
  if (strlen($words[$rand_key]) > 5) {
    $autogenpassword .= $words[$rand_key];
    if ($counter < 3) {
      $autogenpassword .= '-';
    }
    $counter++;
  }
}
writeToLog($link, 'Inserting the new user with email ' . mysqli_real_escape_string($link, $_POST['adduseremail']) . ' to user ledger', $_SESSION['id']);
$sql = "INSERT INTO users (user_first_name, user_last_name, user_email, user_password) VALUE ('" . mysqli_real_escape_string($link, $_POST['adduserfirst']) . "', '" . mysqli_real_escape_string($link, $_POST['adduserlast']) . "', '" . mysqli_real_escape_string($link, $_POST['adduseremail']) . "', '" . password_hash($autogenpassword, PASSWORD_DEFAULT) . "')";
if ($link->query($sql) === TRUE) {
  writeToLog($link, 'Created new user ' . mysqli_real_escape_string($link, $_POST['adduseremail']), $_SESSION['id']);
  echo '<p>Created new user: ' . mysqli_real_escape_string($link, $_POST['adduserfirst']) . '&nbsp;' . mysqli_real_escape_string($link, $_POST['adduserlast']) . '</p>';
  echo '<p>' . mysqli_real_escape_string($link, $_POST['adduserfirst']) . "'s temporary password is <code>" . $autogenpassword . "</code>. Make sure you record this temporary password now, as you cannot get it later.</p>";
} else {
  writeToLog($link, 'Failed to create new user', $_SESSION['id']);
  echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
}
?>
      <a href="./" class="btn btn-primary">Admin Portal</a>
      <button class="btn btn-secondary" onclick="history.back()">Go Back</a>
      </div>
    </div>
  </div>
</div>
<?php
include('../templates/_footer.php');
?>
