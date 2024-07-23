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
  <div class="container collapse notransition" id="apikeys" data-bs-parent="#actions">
    <h6 class="my-3">Welcome, <?=$_SESSION['firstname']?>!</h6>
    <h1 class="my-3">Manage Your Personal API Key</h1>
    <form action="updateapikeys.php" method="post">
      <label for="confirmapikeyrotation" class="form-label mb-3">Type "ROTATE MY KEYS" here to confirm you want to rotate your API key.</label>
      <input type="text" name="confirmation" id="confirmapikeyrotation" class="form-control mb-3">
      <button type="submit" class="btn btn-danger">Rotate API Keys</button>
    </form>
    <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#apiguide">API Guide</button>
  </div>
