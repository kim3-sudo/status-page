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
          <button type="submit" class="btn btn-primary">Confirm</button>
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
            <input maxlength="120" name="updatelink" id="update<?=$servicerow['service_id']?>link" class="form-control" value="<?=$subservicerow['service_link']?>">
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
$servicegroupsql = 'SELECT servicegroup_id FROM servicegroups';
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
          <button type="submit" class="btn btn-primary">Confirm</button>
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
            <input class="form-control" id="updateservicegroup<?=$servicegrouprow['servicegroup_id']?>name" name="updateservicegroupname" type="text">
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
