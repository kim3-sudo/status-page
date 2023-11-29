<!-- Are you the system admin? Edit this text to reflect your organization's style guide. -->
<ul class="mb-3">
  <li><b>Use the present tense where possible.</b> Instead of <em>Users may have noticed</em>, use <em>Users may notice</em> instead.</li>
  <li><b>Use succinct but exact language.</b> Instead of <em>Users who experience issues authenticating to Shibboleth-authenticated services and getting to two-factor authentication using Duo</em>, use <em>Users experiencing authentication issues</em> instead.</li>
  <li><b>Avoid using pronouns in general.</b> Instead of <em>you</em>, use <em>users</em>, <em>administrators</em>, or a more exact term instead.</li>
  <li><b>In connection with being succinct, avoid using overly technical language.</b> Instead of <em>authentication to SMB file shares on a network drive</em>, use <em>authentication to network drives</em> instead.</li>
</ul>
<p class="mb-3">The incident description should contain a brief description of the issue at hand. You can think of the incident description as a headline or a title for the incident. The incident description stays consistent throughout the entire incident and cannot be changed.</p>
<p>Examples:</p>
<ul class="list-group mb-3">
  <li class="list-group-item">Access Issue with Banner</li>
  <li class="list-group-item">Wireless Connectivity Issue</li>
  <li class="list-group-item">EMS not loading properly for some users</li>
  </ul>
<p class="mb-3">The incident update should contain the detailed information about the issue at hand. You can think of the incident update as the body to the incident. It should contain the issue, symptoms that the user may experience, and any workarounds.</p>
<p>Examples:</p>
<ul class="list-group mb-3">
  <li class="list-group-item">We have identified an access issue with Banner and are working to identify the issue. When users try to connect to Banner or to Personal Access Pages, users may experience an error message or just a blank screen. We are working to identify and recify the issue as quickly as possible. We have not identified a workaround yet but are working to diagnose the issue. We will leave updates here when we learn more information and as we implement fixes.</li>
  <li class="list-group-item">We have implemented a fix and have determined that the issue with wireless connectivity has been resolved. Users who continue to have issues should contact our help desk for further assistance. We apologize for this disruption in service, and we appreciate your patience and understanding. We will continue to work and improve to make sure this doesn't happen again. We have not identified a workaround yet but are working to diagnose the issue. We will continue to leave updates here when we learn more information and as we implement fixes.</li>
  <li class="list-group-item">Our team deployed a minor technical change to the handling of certificates. During this change, we saw a higher-than-anticipated number of certificate errors which resulted in inaccessible remote servers. The issue was resolved when our certificate authority pushed out a new set of valid certificates.</li>
</ul>
<p class="mb-3">When creating an incident, it must be assigned a status. The status codes are provided here.</p>
<ul class="mb-3">
  <li><b>Identified</b> means that IT recognizes that something is wrong, but a cause or repair has not been determined yet.</li>
  <li><b>Investigating</b> means that IT is actively working on a diagnosis and resolution to the incident.</li>
  <li><b>Monitoring</b> means that a resolution has been implemented, but IT wants to carefully monitor the health of the service.</li>
  <li><b>Resolved</b> means that a resolution has been implemented, and the incident is over.</li>
</ul>
<p class="mb-3">During an incident, services may be affected at different levels. Several levels have been defined.</p>
<ul class="mb-3">
  <li><b>Operational</b> service indicates that a service is working normally.</li>
  <li><b>Degraded</b> tiers of service indicate that a service is acting normally, aside from a noticeable decrease in performance, speed, redundancy, or availability level. For example, if the firewall has failed over to a 1Gb link from its normal 10Gb link, this would indicate degraded performance. The service still works, but it is slower than normal.</li>
  <li><b>Minor outages</b> indicate that a service is experiencing errors for some or many people. Certain modules may not be functioning correctly. Some users who don't use any of these features may still be able to use the system, but others may be affected. For example, if there is a partial outage that affects only certain environments in VMware Horizon (like all of the math lab computers, but not general lab), then the status would probably be minor outage.</li>
  <li><b>Major outages</b> indicate that a service is completely down or that a majority of users are experiencing issues with the system. Most users will not be able to use the service normally.</li>
</ul>
