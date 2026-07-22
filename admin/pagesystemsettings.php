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
<?php require('_guard.php'); ?>
<div class="container collapse notransition" id="systemsettings" data-bs-parent="#actions">
  <h6 class="my-3">Welcome, <?=$_SESSION['firstname']?>!</h6>
  <h1 class="my-3">System Settings</h1>
  <div class="rounded border p-3 my-2">
    <p>General System Settings</p>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-footer_org" value="footer_org">
      <label for="organizationupdate" class="form-label">Organization</label>
      <div class="input-group">
<?php
$organization = getSetting($link, 'footer_org');
?>
        <input type="text" id="organizationupdate" name="setting_value" class="form-control" placeholder="Organization" aria-label="Organization" aria-describedby="Organization" value="<?=$organization?>">
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3"><small>This text is used for the copyright owner and some other filler texts.</small></p>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-org_link" value="org_link">
      <label for="orglinkupdate" class="form-label">Organization Webpage/Link</label>
      <div class="input-group">
<?php
$orglink = getSetting($link, 'org_link');
?>
        <input type="text" id="orglinkupdate" name="setting_value" class="form-control" placeholder="Organization Link" aria-label="Organization Link" aria-describedby="Organization Link" value="<?=$orglink?>">
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3"><small>This address is linked to from the footer bar when your organization name is clicked.</small></p>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-feedback_link" value="feedback_link">
      <label for="feedbacklinkupdate" class="form-label">Feedback Webpage/Link</label>
      <div class="input-group">
<?php
$feedbacklink = getSetting($link, 'feedback_link');
?>
        <input type="text" id="feedbacklinkupdate" name="setting_value" class="form-control" placeholder="Feedback Link" aria-label="Feedback Link" aria-describedby="Feedback Link" value="<?=$feedbacklink?>">
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3"><small>This address is linked to under Feedback in the footer. It should either be a web address or a <code>mailto:</code>.</small></p>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-privacy_policy_link" value="privacy_policy_link">
      <label for="privacypolicylinkupdate" class="form-label">Privacy Policy Webpage/Link</label>
      <div class="input-group">
<?php
$privacypolicylink = getSetting($link, 'privacy_policy_link');
?>
        <input type="text" id="privacypolicylinkupdate" name="setting_value" class="form-control" placeholder="Privacy Policy Link" aria-label="Privacy Policy Link" aria-describedby="Privacy Policy Link" value="<?=$privacypolicylink?>">
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3"><small>This address is linked to under Privacy Policy in the footer.</small></p>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-header_image_location" value="header_image_location">
      <label for="headerimageupdate" class="form-label">Header Image URL</label>
      <div class="input-group">
<?php
$headerimage = getSetting($link, 'header_image_location');
?>
        <input type="text" id="headerimageupdate" name="setting_value" class="form-control" placeholder="Header Image URL" aria-label="Header Image URL" aria-describedby="Header Image URL" value="<?=$headerimage?>">
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3"><small>This address should point to a logo or logotype for your organization. Valid extensions include <code>png</code>, <code>webp</code>, and <code>jpg</code>.</small></p>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-get_help_destination" value="get_help_destination">
      <label for="gethelpupdate" class="form-label">Get Help Destination</label>
      <div class="input-group">
<?php
$gethelpdestination = getSetting($link, 'get_help_destination');
?>
        <input type="text" id="gethelpupdate" name="setting_value" class="form-control" placeholder="Get Help Destination" aria-label="Get Help Destination" aria-describedby="Get Help Destination" value="<?=$gethelpdestination?>">
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3"><small>This address is linked to the 'Get Help' button in the header.</small></p>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-pes_description" value="pes_description">
      <label for="pesdescriptionupdate" class="form-label">PES Description</label>
      <div class="input-group">
<?php
$pesdescription = getSetting($link, 'pes_description');
?>
        <input type="text" id="pesdescriptionupdate" name="setting_value" class="form-control" placeholder="PES Description" aria-label="PES Description" aria-describedby="PES Description" value="<?=$pesdescription?>">
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3"><small>This text is printed as a description for the post-event summary page.</small></p>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-about_this_site" value="about_this_site">
      <label for="aboutthissiteupdate" class="form-label">About This Site</label>
      <div class="input-group">
<?php
$aboutthissite = getSetting($link, 'about_this_site');
?>
        <input type="text" id="aboutthissiteupdate" name="setting_value" class="form-control" placeholder="About this site" aria-label="About this site" aria-describedby="About this site" value="<?=$aboutthissite?>">
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3"><small>This text is printed on the status page as a reference for readers. If left blank, this section will not print.</small></p>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-meta_description" value="meta_description">
      <label for="metadescriptionupdate" class="form-label">Meta Description</label>
      <div class="input-group">
<?php
$metadescription = getSetting($link, 'meta_description');
?>
        <input type="text" id="metadescriptionupdate" name="setting_value" class="form-control" placeholder="Meta Description" aria-label="Meta Description" aria-describedby="Meta description" value="<?=$metadescription?>">
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3"><small>This text is provided for search engines as the site description in a meta tag. If left blank, no description will be provided in the document head.</small></p>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-ga_measurement_id" value="ga_measurement_id">
      <label for="gameasurementidupdate" class="form-label">Google Analytics Measurement ID</label>
      <div class="input-group">
<?php
$gameasurementid = getSetting($link, 'ga_measurement_id');
?>
        <input type="text" id="gameasurementidupdate" name="setting_value" class="form-control" placeholder="GA Measurement ID" aria-label="GA Measurement ID" aria-describedby="Google Analytics Measurement ID" value="<?=$gameasurementid?>">
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3"><small>Paste your Google Analytics measurement ID here. If you do not use Google Analytics, leave this blank.</small></p>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-welcome_message" value="welcome_message">
      <label for="welcomemessageupdate" class="form-label">Welcome Message</label>
      <div class="input-group">
<?php
$welcomemessage = getSetting($link, 'welcome_message');
?>
        <input type="text" id="welcomemessageupdate" name="setting_value" class="form-control" placeholder="Welcome Message" aria-label="Welcome Message" aria-describedby="Welcome Message" value="<?=$welcomemessage?>">
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3"><small>If this welcome message is set, a modal will open with the message text on status page load.</small></p>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-timezone" value="timezone">
      <label for="timezoneupdate" class="form-label">Timezone</label>
      <div class="input-group">
        <select class="form-control" name="setting_value" id="timezoneupdate">
<?php
$activetimezone = getSetting($link, 'timezone');
foreach (timezone_identifiers_list() as $timezonerow) {
  if ($activetimezone == $timezonerow) {
    echo '<option value="' . $timezonerow . '" selected>' . $timezonerow . '</option>';
  } else {
    echo '<option value="' . $timezonerow . '">' . $timezonerow . '</option>';
  }
}
?>
        </select>
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-plannedfuturedays" value="plannedfuturedays">
      <label for="plannedfuturedaysupdate" class="form-label">Planned Maintenance Preview Days</label>
      <div class="input-group">
        <select class="form-control" name="setting_value" id="plannedfuturedaysupdate">
<?php
$activeplannedfutureday = getSetting($link, 'plannedfuturedays');
foreach (array(1, 3, 7, 14, 30, 60) as $dayrange) {
  if ($dayrange == $activeplannedfutureday) {
    echo '<option value="' . $dayrange . '" selected>' . $dayrange . ' days</option>';
  } else {
    echo '<option value="' . $dayrange . '">' . $dayrange . ' days</option>';
  }
}
?>
        </select>
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3"><small>Planned maintenance messages will not display in the messages until a certain number of days defined here.</small></p>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-incidenttoshowtimerange" value="incident_to_show_timerange">
      <label for="incidenttoshowtimerangeupdate" class="form-label">Incident To Show Timerange</label>
      <div class="input-group">
        <select class="form-control" name="setting_value" id="incidenttoshowtimerangeupdate">
<?php
$activetimerange = getSetting($link, 'incident_to_show_timerange');
foreach (array(30, 60, 90, 120) as $dayrange) {
  if ($dayrange == $activetimerange) {
    echo '<option value="' . $dayrange . '" selected>' . $dayrange . ' days</option>';
  } else {
    echo '<option value="' . $dayrange . '">' . $dayrange . ' days</option>';
  }
}
?>
        </select>
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3"><small>Incidents will no longer display after the number of days defined here.</small></p>
    </form>
  </div>
  <div class="rounded border p-3 my-2">
    <p>Single Sign-On Settings</p>
<?php
$spMetaBaseUrl = getSetting($link, 'service_provider_base_url');
if (!empty($spMetaBaseUrl)):
?>
    <div class="alert alert-info py-2 mb-3">
      <strong>SP Metadata URL:</strong>
      <a href="<?=htmlspecialchars(rtrim($spMetaBaseUrl, '/') . '/saml/metadata.php')?>" target="_blank" rel="noopener noreferrer">
        <?=htmlspecialchars(rtrim($spMetaBaseUrl, '/') . '/saml/metadata.php')?>
      </a><br>
      <small class="text-muted">Provide this URL to your Identity Provider (IdP) to register this application as a Service Provider.</small>
    </div>
<?php endif; ?>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-enable_sso" value="enable_sso">
      <label for="enablessoupdate" class="form-label">Enable SAML Single Sign-On</label>
      <div class="input-group">
<?php
$enablesso = getSetting($link, 'enable_sso');
if ($enablesso == 'true') {
?>
<select id="enablessoupdate" name="setting_value" class="form-control">
  <option value="true" selected>Yes</option>
  <option value="false">No</option>
</select>
<?php
} else {
?>
<select id="enablessoupdate" name="setting_value" class="form-control" required>
  <option value="true">Yes</option>
  <option value="false" selected>No</option>
</select>
<?php
}
?>
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3" for="baseurlupdate"><small>If this is set to No, then the below settings do not apply.</small></p>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-service_provider_base_url" value="service_provider_base_url">
      <label for="baseurlupdate" class="form-label">Service Provider Base URL</label>
      <div class="input-group">
<?php
$spBaseUrl = getSetting($link, 'service_provider_base_url');
?>
        <input type="text" id="baseurlupdate" name="setting_value" class="form-control" placeholder="Service Provider Base URL" aria-label="Service Provider Base URL" aria-describedby="Service Provider Base URL" value="<?=$spBaseUrl?>" required>
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3" for="baseurlupdate"><small>This should be set to the FQDN of the server running this software without the trailing slash.</small></p>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-name_id_format" value="name_id_format">
      <label for="nameidformatupdate" class="form-label">Name ID Format</label>
      <div class="input-group">
<?php
$NameIDFormat = getSetting($link, 'name_id_format');
?>
        <input type="text" id="nameidformatupdate" name="setting_value" class="form-control" placeholder="Name ID Format" aria-label="Name ID Format" aria-describedby="Name ID Format" value="<?=$NameIDFormat?>" required>
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3" for="nameidformatupdate"><small>If you are not sure, use <code>urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified</code>.</small></p>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-entity_id" value="entity_id">
      <label for="entityidupdate" class="form-label">Entity ID</label>
      <div class="input-group mb-3">
<?php
$entityid = getSetting($link, 'entity_id');
?>
        <input type="text" id="entityidupdate" name="setting_value" class="form-control" placeholder="Entity ID" aria-label="Entity ID" aria-describedby="Entity ID" value="<?=$entityid?>" required>
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-sso_service" value="sso_service">
      <label for="ssoserviceupdate" class="form-label">SSO Service</label>
      <div class="input-group mb-3">
<?php
$ssoservice = getSetting($link, 'sso_service');
?>
        <input type="text" id="ssoserviceupdate" name="setting_value" class="form-control" placeholder="SSO Service" aria-label="SSO Service" aria-describedby="SSO Service" value="<?=$ssoservice?>" required>
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-slo_service" value="slo_service">
      <label for="sloserviceupdate" class="form-label">SLO Service</label>
      <div class="input-group mb-3">
<?php
$sloservice = getSetting($link, 'slo_service');
?>
        <input type="text" id="sloserviceupdate" name="setting_value" class="form-control" placeholder="SLO Service" aria-label="SLO Service" aria-describedby="SLO Service" value="<?=$sloservice?>" required>
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-x509cert" value="x509cert">
      <label for="x509certificateupdate" class="form-label">X509 Certificate</label>
<?php
$x509certificate = getSetting($link, 'x509cert');
?>
      <textarea id="x509certificateupdate" name="setting_value" class="form-control font-monospace mb-2" rows="4"
        placeholder="Paste the base64-encoded certificate content here (without -----BEGIN/END CERTIFICATE----- headers)"
        aria-label="X509 Certificate" required><?=htmlspecialchars($x509certificate ?? '')?></textarea>
      <p class="text-muted mb-2"><small>Paste the raw base64-encoded certificate body provided by your IdP. Do <strong>not</strong> include the <code>-----BEGIN CERTIFICATE-----</code> / <code>-----END CERTIFICATE-----</code> header and footer lines.</small></p>
      <button class="btn btn-primary" type="submit">Submit</button>
    </form>
    <form action="updatesettings.php" method="post">
      <input type="hidden" name="setting_key" id="systemkey-saml_email_attribute" value="saml_email_attribute">
      <label for="samlemailattributeupdate" class="form-label">SAML Email Attribute</label>
      <div class="input-group mb-2">
<?php
$samlemailattr = getSetting($link, 'saml_email_attribute');
?>
        <input type="text" id="samlemailattributeupdate" name="setting_value" class="form-control"
          placeholder="e.g. email, mail, or a full URN attribute name (optional)"
          aria-label="SAML Email Attribute" value="<?=htmlspecialchars($samlemailattr)?>">
        <button class="btn btn-primary" type="submit">Submit</button>
      </div>
      <p class="text-muted mb-3"><small>
        The name of the SAML attribute whose value holds the user's email address
        (e.g. <code>email</code>, <code>mail</code>, or
        <code>http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress</code>).
        Leave blank to use the SAML NameID as the email address.
        The resolved email must match a user account in the Admin Users list.
      </small></p>
    </form>
  </div>
</div>
