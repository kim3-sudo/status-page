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
$servicesql = 'SELECT service_id FROM services';
$serviceresult = mysqli_query($link, $servicesql);
if (mysqli_num_rows($serviceresult) > 0) {
  while ($servicerow = mysqli_fetch_assoc($serviceresult)) {
?>
<div class="modal fade" id="servicemodal<?=$servicerow['service_id']?>delete" tabindex="-1" aria-labelledby="servicemodal<?=$servicerow['service_id']?>deletehelp" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="servicemodal<?=$servicerow['service_id']?>deletehelp">Delete Confirmation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
      </div>
      <div class="modal-body">
        <form action="deleteservice.php" method="post">
          <input type="hidden" name="deleteserviceid" value="<?=$servicerow['service_id']?>">
          <label class="form-label">Delete service with ID <?=$servicerow['service_id']?>?</label>
          <button type="submit" class="btn btn-danger">Delete</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php
    $subservicesql = 'SELECT service_id, service_name, servicegroup_id, service_description, service_link FROM services WHERE service_id = ' . $servicerow['service_id'];
    $subserviceresult = mysqli_query($link, $subservicesql);
    if ($subservicerow = mysqli_fetch_assoc($subserviceresult)) {
?>
<div class="modal fade" id="servicemodal<?=$servicerow['service_id']?>" tabindex="-1" aria-labelledby="servicemodal<?=$servicerow['service_id']?>label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="servicemodal<?=$servicerow['service_id']?>label"><?=$subservicerow['service_name']?> Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
      </div>
      <div class="modal-body">
        <form action="updateservice.php" method="post">
          <input type="hidden" id="update<?=$servicerow['service_id']?>id" name="updateid" value="<?=$servicerow['service_id']?>">
          <div class="mb-3">
            <label for="update<?=$servicerow['service_id']?>name" class="form-label">Update Name<span class="required">*</span></label>
            <input type="text" class="form-control" id="update<?=$servicerow['service_id']?>name" name="updatename" value="<?=$subservicerow['service_name']?>">
          </div>
          <div class="mb-3">
            <label for="update<?=$servicerow['service_id']?>group" class="form-label">Update Group<span class="required">*</span></label>
            <select class="form-control" id="update<?=$servicerow['service_id']?>group" name="updategroup">
<?php
      $subservicegroupsql = 'SELECT servicegroup_id, servicegroup_name FROM servicegroups ORDER BY servicegroup_name ASC';
      $subservicegroupresult = mysqli_query($link, $subservicegroupsql);
      if (mysqli_num_rows($subservicegroupresult) > 0) {
        while ($subservicegrouprow = mysqli_fetch_assoc($subservicegroupresult)) {
          if ($subservicegrouprow['servicegroup_id'] == $subservicerow['servicegroup_id']) {
            echo '<option selected value="' . $subservicegrouprow['servicegroup_id'] . '">' . $subservicegrouprow['servicegroup_name'] . '</option>';
          } else {
            echo '<option value="' . $subservicegrouprow['servicegroup_id'] . '">' . $subservicegrouprow['servicegroup_name'] . '</option>';
          }
        }
      }
?>
            </select>
          </div>
          <div class="mb-3">
            <label for="update<?=$servicerow['service_id']?>description" class="form-label">Update Description</label>
            <textarea maxlength="144" name="updatedescription" id="update<?=$servicerow['service_id']?>description" class="form-control"><?=$subservicerow['service_description']?></textarea>
          </div>
          <div class="mb-3">
            <label for="update<?=$servicerow['service_id']?>link" class="form-label">Update Link</label>
            <input type="url" maxlength="120" name="updatelink" id="update<?=$servicerow['service_id']?>link" class="form-control" value="<?=$subservicerow['service_link']?>">
          </div>
          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php
    } else {
?>
<div class="modal fade" id="servicemodal<?=$subservicerow['service_id']?>" tabindex="-1" aria-labelledby="servicemodal<?=$subservicerow['service_id']?>label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="servicemodal<?=$subservicerow['service_id']?>label">Service detail error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Error: details for service with ID <?=$subservicerow['service_id']?> are unavailable or could not be fetched.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php
    }
  }
} else {
  echo '';
}
$servicegroupsql = 'SELECT servicegroup_id, servicegroup_name FROM servicegroups';
$servicegroupresult = mysqli_query($link, $servicegroupsql);
if (mysqli_num_rows($serviceresult) > 0) {
  while ($servicegrouprow = mysqli_fetch_assoc($servicegroupresult)) {
?>
<div class="modal fade" id="servicegroup<?=$servicegrouprow['servicegroup_id']?>delete" tabindex="-1" aria-labelledby="servicegroup<?=$servicegrouprow['servicegroup_id']?>deletelabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-label" id="servicegroup<?=$servicegrouprow['servicegroup_id']?>deletelabel">Delete Confirmation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
      </div>
      <div class="modal-body">
        <form action="deleteservicegroup.php" method="post">
          <input type="hidden" name="deleteservicegroupid" value="<?=$servicegrouprow['servicegroup_id']?>">
          <label class="form-label">Delete service group with ID <?=$servicegrouprow['servicegroup_id']?>?</label>
          <button type="submit" class="btn btn-danger">Delete</button>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php
    $subsgsql = 'SELECT servicegroup_id, servicegroup_name FROM servicegroups';
    $subsgresult = mysqli_query($link, $subsgsql);
    if ($subsgrow = mysqli_fetch_assoc($subsgresult)) {
?>
<div class="modal fade" id="servicegroup<?=$servicegrouprow['servicegroup_id']?>modal" tabindex="-1" aria-labelledby="servicegroup<?=$servicegrouprow['servicegroup_id']?>modallabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="servicegroup<?=$servicegrouprow['servicegroup_id']?>modallabel"><?=$subsgrow['servicegroup_name']?> Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
      </div>
      <div class="modal-body">
        <form action="updateservicegroup.php" method="post">
          <input type="hidden" id="updateservicegroup<?=$servicegrouprow['servicegroup_id']?>id" name="updateservicegroupid" value="<?=$servicegrouprow['servicegroup_id']?>">
          <div class="mb-3">
            <label for="updateservicegroup<?=$servicegrouprow['servicegroup_id']?>name" class="form-label">Update Service Group Name<span class="required">*</span></label>
            <input class="form-control" id="updateservicegroup<?=$servicegrouprow['servicegroup_id']?>name" name="updateservicegroupname" value="<?=$servicegrouprow['servicegroup_name']?>" type="text">
          </div>
          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php
    } else {
?>
<div class="modal fade" id="servicegroup<?=$servicegrouprow['servicegroup_id']?>modal" tabindex="-1" aria-labelledby="servicegroup<?=$servicegrouprow['servicegroup_id']?>modallabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="servicegroup<?=$servicegrouprow['servicegroup_id']?>modallabel">Service group detail error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Error: details for service group with ID <?=$servicegrouprow['servicegroup_id']?> are unavailable or could not be fetched.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php
    }
  }
} else {
  echo '';
}
?>
<?php
$usersql = 'SELECT user_id, user_last_name, user_first_name, user_email FROM users';
$userresult = mysqli_query($link, $usersql);
while ($userrow = mysqli_fetch_assoc($userresult)) {
?>
<div class="modal fade" id="user<?=$userrow['user_id']?>modal" tabindex="-1" aria-labelledby="user<?=$userrow['user_id']?>modallabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="user<?=$userrow['user_id']?>modallabel"><?=$userrow['user_first_name']?>&nbsp;<?=$userrow['user_last_name']?>'s Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
      </div>
      <div class="modal-body">
        <form action="updateuser.php" method="post">
          <input type="hidden" id="updateuser<?=$userrow['user_id']?>id" name="updateuserid" value="<?=$userrow['user_id']?>">
          <div class="mb-3">
            <label for="updateuser<?=$userrow['user_id']?>firstname" class="form-label">Update First Name<span class="required">*</span></label>
            <input class="form-control" id="updateuser<?=$userrow['user_id']?>firstname" name="updateuserfirstname" type="text" value="<?=$userrow['user_first_name']?>">
          </div>
          <div class="mb-3">
            <label for="updateuser<?=$userrow['user_id']?>lastname" class="form-label">Update Last Name<span class="required">*</span></label>
            <input class="form-control" id="updateuser<?=$userrow['user_id']?>lastname" name="updateuserlastname" type="text" value="<?=$userrow['user_last_name']?>">
          </div>
          <div class="mb-3">
            <label for="updateuser<?=$userrow['user_id']?>email" class="form-label">Update Email Address<span class="required">*</span></label>
            <input class="form-control" id="updateuser<?=$userrow['user_id']?>email" name="updateuseremail" type="email" value="<?=$userrow['user_email']?>">
          </div>
<?php
if ($_SESSION['suflag'] == '1') {
?>
          <div class="mb-3">
            <label for="updateuser<?=$userrow['user_id']?>password" class="form-label">Update Password</label>
            <input class="form-control" id="updateuser<?=$userrow['user_id']?>password" name="updateuserpassword" type="password" placeholder="Leave blank to leave unchanged">
          </div>
<?php
}
?>
          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
<?php
if ($_SESSION['suflag'] == '1') {
  $sql = "SELECT user_totpenabled FROM users WHERE user_id = " . $userrow['user_id'];
  $enrollment = mysqli_fetch_assoc(mysqli_query($link, $sql))['user_totpenabled'];
  if ($enrollment == 1) {
?>
        <form action="updatetotpadmin.php" method="post" class="mt-3">
          <input type="hidden" name="totpuserid" value="<?=$userrow['user_id']?>">
          <label class="form-label">Resetting 2FA configuration will immediately disable two-factor authentication for this user! The user must re-enroll in two-factor authentication themselves.</label>
          <button type="submit" class="btn btn-danger">Reset 2FA For User</button>
        </form>
<?php
  } else {
    echo '<p class="form-label mt-3">This user is not enrolled in two-factor authentication.</p>';
  }
}
?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php
$sql = 'SELECT apikeys_id, apikeys_nickname FROM apikeys WHERE apikeys_user_id = ' . $_SESSION['id'];
$result = mysqli_query($link, $sql);
if (mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
?>
<div class="modal fade" id="serviceapi<?=$row['apikeys_id']?>delete" tabindex="-1" aria-labelledby="serviceapi<?=$row['apikeys_id']?>deletelabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-label" id="serviceapi<?=$row['apikeys_id']?>deletelabel">Delete Confirmation</h5>
        <button type="button" class="btn-close" data-bs-disimss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="deleteserviceapikey.php" method="post">
          <input type="hidden" name="keyid" value="<?=$row['apikeys_id']?>">
          <label class="form-label">Delete service API key with ID <?=$row['apikeys_id']?>?</label>
          <button type="submit" class="btn btn-danger">Delete</button>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php
  }
}
?>
<div class="modal fade" id="user<?=$userrow['user_id']?>delete" tabindex="-1" aria-labelledby="user<?=$userrow['user_id']?>deletelabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-label" id="user<?=$userrow['user_id']?>deletelabel">Delete Confirmation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
      </div>
      <div class="modal-body">
        <form action="deleteuser.php" method="post">
          <input type="hidden" name="deleteuserid" value="<?=$userrow['user_id']?>">
          <label class="form-label">Delete user with ID <?=$userrow['user_id']?>?</label>
          <button type="submit" class="btn btn-danger">Delete</button>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php
}
?>
<div class="modal fade" id="messagestylemodal" tabindex="-1" aria-labelledby="messagestylelabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-fullscreen-lg-down modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="messagestylelabel">Message Style Guide and Examples</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">This style guide is provided to keep the messaging for incidents consistent, regardless of who is writing the message. Examples are also provided as part of this style guide.</p>
<?php
include('_incidentstyleguide.php');
?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="pesstyle" tabindex="-1" aria-labelledby="pesmodallabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-fullscreen-lg-down modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pesmodallabel">Post-Event Summary Style Guide</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">This style guide is provided to keep the style of post-event summaries consistent, regardless of who is writing or publishing them.</p>
<?php
include('_pesstyleguide.php');
?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="twofactorwarning" tabindex="-1" aria-labelledby="twofactorwarninglabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="twofactorwarninglabel">Two-Factor Warning</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">This account is not enrolled in two-factor authentication. Enroll on the <b>Update Password</b> page, then log out and back in.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Acknowledge</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="apiguide" tabindex="-1" aria-labelledby="apiguidelabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="apiguidelabel">API Programming Guide</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">This RESTful API programming guide will go over the endpoints and their uses. All API endpoints return JSON data.</p>
        <hr class="my-3">
        <h5>Authentication</h5>
        <p>To start using any API endpoints, you'll need to generate an API key for your account or a service account. API keys are widely scoped and cannot be fine-grained (yet).</p>
        <p>To authenticate to an endpoint, use the <code>authkey</code> parameter to pass in your API key as a string. Include the entire API key and nothing more.</p>
        <p>API keys are 128 characters long and consist of uppercase letters, lowercase letters, and numbers. The API key is case-sensitive, and should not contain any ambiguous letters or numbers.</p>
        <hr class="my-3">
        <h5>Service Endpoint&nbsp;<span class="badge" style="background-color: violet; color: white;">GET</span></h5>
        <code>/api/service</code>
        <h6 class="mt-3">Service Status Overview</h6>
        <p>Place a <span class="badge" style="background-color: violet;color: white;">GET</span> request to the endpoint with only the authentication parameter. The returned JSON object looks like:</p>
        <code>
{
  "service_status_short": A three-character code for overall service status,
  "service_status_brief": A one or two word description for overall service status,
  "service_status_description": An English sentence for overall service status
}
        </code>
        <h6 class="mt-3">Specific Service Status</h6>
        <p>Place a <span class="badge" style="background-color: violet;color: white;">GET</span> request to the endpoint with authentication (<code>authkey</code>) and a specific service ID (<code>service_id</code>) as parameters. The returned JSON object looks like:</p>
        <code>
{
  "service_id": The ID of the service, which can be safely typecast to int,
  "service_name": The name of the service,
  "service_description": An optional service description,
  "service_link": An optional link that goes to the service,
  "service_status_short": The three-character code for the service's status,
  "service_status": A one or two word description for the service's status
}
        </code>
        <hr class="my-3">
        <h5>Incident Endpoint&nbsp;<span class="badge" style="background-color: violet;color: white;">GET</span>&nbsp;<span class="badge" style="background-color: lightseagreen; color: white;">POST</span></h5>
        <code>/api/incident</code>
        <h6 class="mt-3">Get Incidents</h6>
        <p>Place a <span class="badge" style="background-color: violet;color: white;">GET</span> request to the endpoint with only the authentication parameter. The returned JSON object contains incident information.
        <h6 class="mt-3">Get Incident Updates</h6>
        <p>Place a <span class="badge" style="background-color: violet;color: white;">GET</span> request to the endpoint with authentication (<code>authkey</code>) and an incident ID (<code>incident_id</code>). The returned JSON object contains all incident updates for the incident ID provided.</p>
        <h6 class="mt-3">Post New Incident</h6>
        <p>Place a <span class="badge" style="background-color: lightseagreen;color: white;">POST</span> request to the endpoint with authentication (<code>authkey</code>), the <code>type</code> set to <code>incident</code>, an incident description (<code>incident_description</code>), incident update HTML text (<code>incident_update</code>), the three-character coded incident status (<code>IDE</code> for identified, <code>INV</code> for investigating, <code>MON</code> for monitoring, and <code>RES</code> for resolved in <code>incident_status</code>), a comma-separated list of affected service IDs (<code>affected_services</code>), and the three-character coded outage severity (<code>MAJ</code> for major outage, <code>MIN</code> for minor outage, <code>DEG</code> for degraded performance in <code>outage_severity</code>). The returned JSON object contains the inserted information, along with the incident ID.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
