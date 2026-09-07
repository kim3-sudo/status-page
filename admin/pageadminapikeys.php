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
  <div class="container collapse notransition" id="serviceapikeys" data-bs-parent="#actions">
    <h6 class="my-3">Welcome, <?=$_SESSION['firstname']?>!</h6>
    <h1 class="my-3">Manage Service API Keys</h1>
    <form action="newserviceapikey.php" method="post">
      <label for="newapikeyname" class="form-label mb-3">New Key Nickname</label>
      <input type="text" name="newapikeyname" id="newapikeyname" class="form-control mb-3" maxlength="128" required>
      <button type="submit" class="btn btn-primary">Generate New Service API Key</button>
    </form>
    <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#apiguide">API Guide</button>
    <hr class="my-3">
    <h2 class="my-3">Service API Keys</h2>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Nickname</th>
          <th>User</th>
          <th>Revoke Key</th>
        </tr>
      </thead>
      <tbody>
<?php
$serviceapi = 'SELECT apikeys_nickname, apikeys_id, users.user_first_name, users.user_last_name FROM apikeys INNER JOIN users ON users.user_id = apikeys.apikeys_user_id WHERE apikeys_is_personal = 0';
$serviceapiresult = mysqli_query($link, $serviceapi);
if (mysqli_num_rows($serviceapiresult) > 0) {
  while ($serviceapirow = mysqli_fetch_assoc($serviceapiresult)) {
?>
        <tr>
          <td>
            <p><?=$serviceapirow['apikeys_nickname']?></p>
          </td>
          <td>
            <p><?=$serviceapirow['user_first_name']?>&nbsp;<?=$serviceapirow['user_last_name']?></p>
          </td>
          <td>
            <button type="button" class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#serviceapi<?=$serviceapirow['apikeys_id']?>delete"><i class="fa-solid fa-xmark text-danger"></i></button>
          </td>
        </tr>
<?php
  }
} else {
  echo '<tr><td>No service API keys</td></tr>';
}
?>
      </tbody>
    </table>
  </div>
