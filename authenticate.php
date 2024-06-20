<?php
session_start();
include('templates/_header.php');
writeToLog($link, 'Authentication request made as user', -1);
writeToLog($link, mysqli_real_escape_string($link, $_POST['email']), -1);
?>
<div class="container">
  <div class="row">
    <div class="col">
<?php
if (!isset($_POST['email'], $_POST['password']) ) {
  writeToLog($link, 'Missing field information', -1);
  exit('Missing field information');
}
writeToLog($link, 'Querying form matching user', -1);
if ($stmt = $link->prepare('SELECT user_id, user_first_name, user_last_name, user_password, user_issuperuser, user_totpenabled FROM users WHERE user_email = ?')) {
  writeToLog($link, 'Query was successful, checking result against form for match', -1);
  $stmt->bind_param('s', $_POST['email']);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    writeToLog($link, 'At least one matching row', -1);
    $stmt->bind_result($id, $firstname, $lastname, $password, $suflag, $totpflag);
    $stmt->fetch();
    writeToLog($link, 'Verifying password', -1);
    if (password_verify($_POST['password'], $password)) {
      writeToLog($link, 'Checking if TOTP 2FA is enabled', -1);
      if ($totpflag == 1) {
        writeToLog($link, 'TOTP 2FA is enabled', -1);
        echo '<form action="twofactorauth.php" method="post" class="my-3">';
        echo '<input type="hidden" name="email" value="' . $_POST['email'] . '">';
        echo '<input type="hidden" name="id" value="' . $id . '">';
        echo '<input type="hidden" name="firstname" value="' . $firstname . '">';
        echo '<input type="hidden" name="lastname" value="' . $lastname . '">';
        echo '<input type="hidden" name="suflag" value="' . $suflag . '">';
        echo '<label for="totpcode" class="form-label mt-2">Two-Factor Authentication: Enter One-Time Passcode</label>';
        echo '<input type="number" max="9999999" name="totpcode" placeholder="0000000" class="form-control mt-2">';
        echo '<button type="submit" class="btn btn-primary mt-2" role="button">Authenticate Two-Factor</button>';
        echo '</form>';
      } else {
        writeToLog($link, 'Password hashes are good, no two-factor, generating session tokens', -1);
        session_regenerate_id();
        $_SESSION['loggedin'] = true;
        $_SESSION['email'] = $_POST['email'];
        $_SESSION['id'] = $id;
        $_SESSION['firstname'] = $firstname;
        $_SESSION['lastname'] = $lastname;
        $_SESSION['suflag'] = $suflag;
        writeToLog($link, 'Redirecting user to admin', -1);
        header('Location: admin/admin.php');
      }
    } else {
      writeToLog($link, 'Authentication failed on password!', -1, 'WARN');
      echo '<p>Incorrect username or password</p>';
    }
  }
} else {
  writeToLog($link, 'Authentication failed on username!', -1, 'WARN');
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
