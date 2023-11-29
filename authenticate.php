<?php
session_start();
include('templates/_header.php');
?>
<div class="container">
  <div class="row">
    <div class="col">
<?php
if (!isset($_POST['email'], $_POST['password']) ) {
  exit('Missing field information');
}
if ($stmt = $link->prepare('SELECT user_id, user_first_name, user_last_name, user_password FROM users WHERE user_email = ?')) {
  $stmt->bind_param('s', $_POST['email']);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $firstname, $lastname, $password);
    $stmt->fetch();
    if (password_verify($_POST['password'], $password)) {
      session_regenerate_id();
      $_SESSION['loggedin'] = true;
      $_SESSION['email'] = $_POST['email'];
      $_SESSION['id'] = $id;
      $_SESSION['firstname'] = $firstname;
      $_SESSION['lastname'] = $lastname;
      header('Location: admin/admin.php');
    } else {
      echo '<p>Incorrect username or password</p>';
    }
  }
} else {
  echo '<p>Incorrect username or password</p>';
}
$stmt->close();
?>
    </div>
  </div>
</div>
<?php
include('templates/_footer.php');
?>
