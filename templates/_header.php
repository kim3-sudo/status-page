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
session_start();
include_once('config.php');
require_once(__DIR__ . '/_db_helpers.php');
$static_base_url = $_SERVER['DOCUMENT_ROOT'];

$timezonerow = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'timezone'"));
date_default_timezone_set($timezonerow['setting_value']);

function renderUpdateContent($description) {
  if (str_starts_with($description, '<p>')) {
    echo $description;
  } else {
    echo '<p>' . $description . '</p>';
  }
}
?>
<!doctype html>
<html lang="en">
<head>
<!-- Well hello there. If you're reading this, perhaps you want a copy of this software? -->
<!-- Find it on GitHub at https://github.com/kim3-sudo/status-page -->
<!-- Made with <3 in Gambier, Ohio -->
<!-- Software version <?=getSetting($link, 'software_version')?> -->
<!-- Database version <?=getSetting($link, 'database_version')?> -->
<?php
$ga_id = getSetting($link, 'ga_measurement_id');
if ($ga_id != ''):
?>
<!-- Google tag (gtag.js) -->
<script async src="https://googletagmanager.com/gtag/js?id=<?=$ga_id?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?=$ga_id?>');
</script>
<?php else: ?>
<!-- No Google tag detected in system configuration -->
<?php endif; ?>
  <title><?=getSetting($link, 'footer_org')?> Service Status</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<?php
$meta_desc = getSetting($link, 'meta_description');
if ($meta_desc != ''):
?>
  <meta name="description" content="<?=$meta_desc?>">
<?php endif; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/fontawesome/css/fontawesome.css">
  <link rel="stylesheet" href="/assets/fontawesome/css/brands.css">
  <link rel="stylesheet" href="/assets/fontawesome/css/solid.css">
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="d-flex flex-column h-100 bg-light">
  <header class="bg-dark p-5 d-flex flex-wrap align-items-center justify-content-center justify-content-md-between">
    <?php $org_name = getSetting($link, 'footer_org'); ?>
    <a href="/">
      <h1 class="visually-hidden"><?=$org_name?> IT Status Page</h1>
      <img class="d-flex align-items-center mb-2 mb-lg-0" src="<?=getSetting($link, 'header_image_location')?>" alt="<?=$org_name?>'s logo or logotype. This alt text was automatically generated." width="102">
    </a>
    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start">
      <ul class="col nav text-end d-none d-md-block">
        <li class="nav-item">
          <a href="<?=getSetting($link, 'get_help_destination')?>" class="btn btn-secondary me-2">Get Help</a>
        </li>
      </ul>
    </div>
  </header>
  <main class="flex-shrink-0">
