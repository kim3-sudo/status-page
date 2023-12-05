<?php
session_start();
include_once('config.php');
$static_base_url = $_SERVER['DOCUMENT_ROOT'];
?>
<!doctype html>
<html lang="en">
<head>
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
