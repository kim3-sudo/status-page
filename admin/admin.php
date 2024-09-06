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
if (!isset($_SESSION['id'])) {
  header('Location: ../login.php');
}
include('../templates/_header.php');
writeToLog($link, 'Admin page accessed', $_SESSION['id']);
require_once('../vendor/autoload.php');
use OTPHP\TOTP;
?>
<script src="../vendor/tinymce/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
<div class="d-flex flex-row" style="margin-bottom: 40px;" id="actions">
  <div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 280px;">
    <ul class="list-unstyled ps-0">
      <li class="mb-1">
        <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0" data-bs-toggle="collapse" data-bs-target="#incident-collapse" aria-expanded="true">Incidents and Maintenance</button>
        <div class="collapse show" id="incident-collapse">
          <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
            <li><a href="#addincident" role="button" data-bs-toggle="collapse" aria-controls="addincident" class="link-body-emphasis d-inline-flex text-decoration-none rounded" aria-current="page">Add Incident</a></li>
            <li><a href="#updateincident" role="button" data-bs-toggle="collapse" aria-controls="updateincident" class="link-body-emphasis d-inline-flex text-decoration-none rounded" aria-current="page">Update Incident</a></li>
            <li><a href="#plannedmaintenance" role="button" data-bs-toggle="collapse" aria-controls="plannedmaintenance" class="link-body-emphasis d-inline-flex text-decoration-none rounded" aria-current="page">Planned Maintenance</a></li>
          </ul>
        </div>
      </li>
      <li class="mb-1">
        <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0" data-bs-toggle="collapse" data-bs-target="#service-collapse" aria-expanded="false">Services</button>
        <div class="collapse" id="service-collapse">
          <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
            <li><a href="#manageservices" role="button" data-bs-toggle="collapse" aria-controls="manageservices" class="link-body-emphasis d-inline-flex text-decoration-none rounded" aria-current="page">Manage Services</a></li>
            <li><a href="#managegroups" role="button" data-bs-toggle="collapse" aria-controls="managegroups" class="link-body-emphasis d-inline-flex text-decoration-none rounded" aria-current="page">Manage Service Groups</a>
          </ul>
        </div>
      </li>
      <li class="mb-1">
        <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0" data-bs-toggle="collapse" data-bs-target="#pes-collapse" aria-expanded="false">Post-Event Summaries</button>
        <div class="collapse" id="pes-collapse">
          <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
            <li><a href="#pes" role="button" data-bs-toggle="collapse" aria-controls="pes" class="link-body-emphasis d-inline-flex text-decoration-none rounded" aria-current="page">Post-Event Summaries</a></li>
          </ul>
        </div>
      </li>
      <li class="mb-1">
        <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0" data-bs-toggle="collapse" data-bs-target="#system-collapse" aria-expanded="false">System</button>
        <div class="collapse" id="system-collapse">
          <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
            <li><a href="#systemsettings" role="button" data-bs-toggle="collapse" aria-controls="systemsettings" class="link-body-emphasis d-inline-flex text-decoration-none rounded" aria-current="page">System Settings</a></li>
            <li><a href="#adminuser" role="button" data-bs-toggle="collapse" aria-controls="adminuser" class="link-body-emphasis d-inline-flex text-decoration-none rounded" aria-current="page">Admin Users</a></li>
            <li><a href="#serviceapikeys" role="button" data-bs-toggle="collapse" aria-controls="serviceapikeys" class="link-body-emphasis d-inline-flex text-decoration-none rounded" aria-current="page">Service API Keys</a></li>
          </ul>
        </div>
      </li>
      <li class="border-top my-3"></li>
      <li class="mb-1">
        <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0" data-bs-toggle="collapse" data-bs-target="#account-collapse" aria-expanded="false">Account</button>
        <div class="collapse show" id="account-collapse">
          <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
            <li><a href="#updatepassword" role="button" data-bs-toggle="collapse" aria-controls="updatepassword" class="link-body-emphasis d-inline-flex text-decoration-none rounded" aria-current="page">Update Password</a></li>
            <li><a href="#apikeys" role="button" data-bs-toggle="collapse" aria-controls="apikeys" class="link-body-emphasis d-inline-flex text-decoration-none rounded" aria-current="page">API Keys</a></li>
            <li><a href="logout.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded" aria-current="page">Log out</a>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <p class="small text-muted"><em><?=strtolower($svrow['setting_value'])?>/<?=strtolower($dvrow['setting_value'])?>/<?=strtolower(gethostname())?></em></p>
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
include('pageadminapikeys.php');
include('pageapikeys.php');
include('pagemodals.php');
?>
</div>
<script>
  tinymce.init({
    selector: '#addincidentupdatedescription',
    block_formats: 'Paragraph=p',
    paste_as_text: true,
    plugins: 'link autolink preview',
    promotion: false,
    license_key: 'gpl',
    setup: (editor) => {
      editor.on("change", (e) => {
        const pattern = /\[[a-z/ ]*\?\]/;
        var content = tinymce.activeEditor.getContent("addincidentupdatedescription");
        if (pattern.test(content)) {
          document.getElementById("addincidentplaceholderwarning").classList.add("d-block");
          document.getElementById("addincidentplaceholderwarning").classList.remove("d-none");
        } else {
          document.getElementById("addincidentplaceholderwarning").classList.add("d-none");
          document.getElementById("addincidentplaceholderwarning").classList.remove("d-block");
        }
      });
    }
  });
  tinymce.init({
    selector: '#existingincidentupdate',
    block_formats: 'Paragraph=p',
    paste_as_text: true,
    plugins: 'link autolink preview',
    promotion: false,
    license_key: 'gpl',
    setup: (editor) => {
      editor.on("change", (e) => {
        const pattern = /\[[a-z/ ]*\?\]/;
        var content = tinymce.activeEditor.getContent("existingincidentupdate");
        if (pattern.test(content)) {
          document.getElementById("existingincidentplaceholderwarning").classList.add("d-block");
          document.getElementById("existingincidentplaceholderwarning").classList.remove("d-none");
        } else {
          document.getElementById("existingincidentplaceholderwarning").classList.add("d-none");
          document.getElementById("existingincidentplaceholderwarning").classList.remove("d-block");
        }
      });
    }
  });
  tinymce.init({
    selector: '#plannedmaintenancemessage',
    block_formats: 'Paragraph=p',
    paste_as_text: true,
    plugins: 'link autolink preview',
    promotion: false,
    license_key: 'gpl',
    setup: (editor) => {
      editor.on("change", (e) => {
        const pattern = /\[[a-z/ ]*\?\]/;
        var content = tinymce.activeEditor.getContent("plannedmaintenancemessage");
        if (pattern.test(content)) {
          document.getElementById("plannedmaintenanceplaceholderwarning").classList.add("d-block");
          document.getElementById("plannedmaintenanceplaceholderwarning").classList.remove("d-none");
        } else {
          document.getElementById("plannedmaintenanceplaceholderwarning").classList.add("d-none");
          document.getElementById("plannedmaintenanceplaceholderwarning").classList.remove("d-block");
        }
      });
    }
  });
</script>
<style>
.dropdown-toggle { outline: 0; }

.btn-toggle {
  padding: .25rem .5rem;
  font-weight: 600;
  color: var(--bs-emphasis-color);
  background-color: transparent;
}
.btn-toggle:hover,
.btn-toggle:focus {
  color: rgba(var(--bs-emphasis-color-rgb), .85);
  background-color: var(--bs-tertiary-bg);
}

.btn-toggle::before {
  width: 1.25em;
  line-height: 0;
  content: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='rgba%280,0,0,.5%29' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 14l6-6-6-6'/%3e%3c/svg%3e");
  transition: transform .35s ease;
  transform-origin: .5em 50%;
}

[data-bs-theme="dark"] .btn-toggle::before {
  content: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='rgba%28255,255,255,.5%29' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 14l6-6-6-6'/%3e%3c/svg%3e");
}

.btn-toggle[aria-expanded="true"] {
  color: rgba(var(--bs-emphasis-color-rgb), .85);
}
.btn-toggle[aria-expanded="true"]::before {
  transform: rotate(90deg);
}

.btn-toggle-nav a {
  padding: .1875rem .5rem;
  margin-top: .125rem;
  margin-left: 1.25rem;
}
.btn-toggle-nav a:hover,
.btn-toggle-nav a:focus {
  background-color: var(--bs-tertiary-bg);
}

.scrollarea {
  overflow-y: auto;
}

</style>
<?php
include('../templates/_footer.php');
?>
<?php
if (isset($_SESSION['twofactornotenrolled']) && $_SESSION['twofactornotenrolled'] == 1) {
  echo '<script>new bootstrap.Modal("#twofactorwarning").show();</script>';
}
?>
