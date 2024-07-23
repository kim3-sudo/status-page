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
