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
require('_guard.php');
include('../templates/_header.php');
writeToLog($link, 'Admin page accessed', $_SESSION['id']);
require_once('../vendor/autoload.php');
use OTPHP\TOTP;
?>
<link href="../assets/quill/quill.snow.css" rel="stylesheet">
<script src="../assets/quill/quill.js"></script>
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
        <p class="small text-muted"><em><?=strtolower(getSetting($link, 'software_version'))?>/<?=strtolower(getSetting($link, 'database_version'))?>/<?=strtolower(gethostname())?></em></p>
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
  const editors = [
    { selector: 'addincidentupdatedescription',  warning: 'addincidentplaceholderwarning' },
    { selector: 'existingincidentupdate',         warning: 'existingincidentplaceholderwarning' },
    { selector: 'plannedmaintenancemessage',      warning: 'plannedmaintenanceplaceholderwarning' },
  ];
  const placeholderPattern = /\[[a-z/ ]*\?\]/;
  editors.forEach(({ selector, warning }) => {
    const textarea = document.getElementById(selector);
    const container = document.createElement('div');
    textarea.insertAdjacentElement('afterend', container);

    const quill = new Quill(container, {
      theme: 'snow',
      placeholder: textarea.placeholder || '',
      modules: {
        toolbar: [
          ['bold', 'italic', 'underline'],
          [{ list: 'ordered' }, { list: 'bullet' }],
          [{ align: [] }],
          ['link'],
          ['clean'],
        ],
      },
    });

    // Mirrors TinyMCE's paste_as_text: true — strip formatting from pasted content.
    quill.clipboard.addMatcher(Node.ELEMENT_NODE, (node, delta) => {
      delta.ops = [{ insert: node.innerText || '' }];
      return delta;
    });

    // Initial textarea value is authored as HTML (see plannedmaintenancemessage's
    // embedded <a> tag), so parse it the same way TinyMCE would on init.
    quill.clipboard.dangerouslyPasteHTML(textarea.value);

    quill.on('text-change', () => {
      textarea.value = quill.root.innerHTML;
      const hasPlaceholder = placeholderPattern.test(quill.getText());
      document.getElementById(warning).classList.toggle('d-block', hasPlaceholder);
      document.getElementById(warning).classList.toggle('d-none', !hasPlaceholder);
    });
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
if (isset($_SESSION['twofactornotenrolled']) && $_SESSION['twofactornotenrolled'] == 1) {
  echo '<script>new bootstrap.Modal("#twofactorwarning").show();</script>';
}
?>
