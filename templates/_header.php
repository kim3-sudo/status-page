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
<?php
session_start();
include_once('config.php');
$static_base_url = $_SERVER['DOCUMENT_ROOT'];
$timezonerow = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'timezone'"));
date_default_timezone_set($timezonerow['setting_value']);
function writeToLog($link, $entry, $uid, $type = 'INFO') {
  if ($link->query("INSERT INTO log (log_entry, log_user_id, log_type) VALUES ('" . mysqli_real_escape_string($link, substr($entry, 0, 139)) . "', '" . mysqli_real_escape_string($link, $uid) . "', '" . mysqli_real_escape_string($link, $type) . "')")) {
  } else {
    die('Unable to write to log! Auditability violated.');
  }
}
?>
<!doctype html>
<html lang="en">
<head>
<!-- Well hello there. If you're reading this, perhaps you want a copy of this software? -->
<!-- Find it on GitHub at https://github.com/kim3-sudo/status-page -->
<!-- Made with <3 in Gambier, Ohio -->
<?php
$svrow = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'software_version'"));
$dvrow = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'database_version'"));
?>
<!-- Software version <?=$svrow['setting_value']?> -->
<!-- Database version <?=$dvrow['setting_value']?> -->
<?php
$row = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'ga_measurement_id'"));
if ($row['setting_value'] != '') {
?>
<!-- Google tag (gtag.js) -->
<script async src="https://googletagmanager.com/gtag/js?id=<?=$row['setting_value']?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?=$row["setting_value"]?>');
</script>
<?php
} else {
?>
<!-- No Google tag detected in system configuration -->
<?php
}
$row = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'footer_org'"));
?>
  <title><?=$row['setting_value']?> Service Status</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<?php
$row = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'meta_description'"));
if ($row['setting_value'] != '') {
?>
<meta name="description" content="<?=$row['setting_value']?>">
<?php
}
?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/fontawesome/css/fontawesome.css">
  <link rel="stylesheet" href="/assets/fontawesome/css/brands.css">
  <link rel="stylesheet" href="/assets/fontawesome/css/solid.css">
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="d-flex flex-column h-100 bg-light">
  <header class="bg-dark p-5 d-flex flex-wrap align-items-center justify-content-center justify-content-md-between">
    <a href="/">
<?php
$orow = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'footer_org'"));
?>
      <h1 class="visually-hidden"><?=$orow['setting_value']?> IT Status Page</h1>
<?php
$row = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'header_image_location'"));
?>
      <img class="d-flex align-items-center mb-2 mb-lg-0" src="<?=$row['setting_value']?>" alt="<?=$orow['setting_value']?>'s logo or logotype. This alt text was automatically generated." width="102">
    </a>
    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start">
      <ul class="col nav text-end d-none d-md-block">
        <li class="nav-item">
<?php
$row = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'get_help_destination'"));
?>
          <a href="<?=$row['setting_value']?>" class="btn btn-secondary me-2">Get Help</a>
        </li>
      </ul>
    </div>
  </header>
  <main class="flex-shrink-0">
