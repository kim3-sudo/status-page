<?php
session_start();
if (!isset($_SESSION['id'])) {
  header('Location: ../login.php');
}
include('../templates/_header.php');
writeToLog($link, 'Admin page accessed', $_SESSION['id']);
?>
<div class="d-flex flex-row" style="margin-bottom: 40px;" id="actions">
  <div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 280px;">
    <ul class="nav nav-pills flex-column mb-auto">
      <li class="nav-item">
        <a href="#addincident" role="button" data-bs-toggle="collapse" aria-controls="addincident" class="nav-link" aria-current="page">Add incident</a>
      </li>
        <li class="nav-item">
        <a href="#updateincident" role="button" data-bs-toggle="collapse" aria-controls="updateincident" class="nav-link" aria-current="page">Update incident</a>
      </li>
      <li class="nav-item">
        <a href="#manageservices" role="button" data-bs-toggle="collapse" aria-controls="manageservices" class="nav-link" aria-current="page">Manage services</a>
      </li>
      <li class="nav-item">
        <a href="#managegroups" role="button" data-bs-toggle="collapse" aria-controls="managegroups" class="nav-link" aria-current="page">Manage service groups</a>
      </li>
      <li class="nav-item">
        <a href="#plannedmaintenance" role="button" data-bs-toggle="collapse" aria-controls="plannedmaintenance" class="nav-link" aria-current="page">Planned Maintenance</a>
      </li>
      <li class="nav-item">
        <a href="#pes" role="button" data-bs-toggle="collapse" aria-controls="pes" class="nav-link" aria-current="page">Post-Event Summaries</a>
      </li>
      <li class="nav-item">
        <a href="#systemsettings" role="button" data-bs-toggle="collapse" aria-controls="systemsettings" class="nav-link" aria-current="page">System Settings</a>
      </li>
      <li class="nav-item">
        <a href="#adminuser" role="button" data-bs-toggle="collapse" aria-controls="adminuser" class="nav-link" aria-current="page">Admin Users</a>
      </li>
      <li class="nav-item">
        <a href="#updatepassword" role="button" data-bs-toggle="collapse" aria-controls="updatepassword" class="nav-link" aria-current="page">Update Password</a>
      </li>
      <li class="nav-item">
        <a href="logout.php" class="nav-link" aria-current="page">Log out</a>
      </li>
    </ul>
  </div>
<?php
include('pageaddincident.php');
include('pageupdateincident.php');
include('pagemanageservices.php');
include('pagemanageservicegroups.php');
include('pagepes.php');
include('pageplannedmaintenance.php');
include('pagesystemsettings.php');
include('pageadminuser.php');
include('pageownpassword.php');
include('pagemodals.php');
?>
</div>
<?php
include('../templates/_footer.php');
?>
