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
<!-- Are you the system admin? Edit this text to reflect your organization's style guide. -->
<p>For examples of post-event summaries, please refer to AWS's <a href="https://aws.amazon.com/premiumsupport/technology/pes/">post-event summary</a> page.</p>
<ul class="mb-3">
  <li><b>When describing what happened, use the past tense.</b> Instead of <em>Users experiencing</em>, use <em>Users experienced</em>.</li>
  <li><b>When describing the functionality of a system still in production, use the present tense.</b> Instead of <em>Banner stored data using a caching mechanism</em>, use <em>Banner stores data using a caching mechanism</em>.</li>
  <li><b>Use the active voice.</b> Instead of <em>Users had experienced a delay</em>, use <em>Users experienced a delay</em>.</li>
  <li><b>Avoid using pronouns in general.</b> Instead of <em>you</em>, use <em>users</em> or <em>administrators</em>.</li>
  <li><b>Format dates using the month, ordinal digit day, and full year.</b> For example, use <em>July 11th, 2023</em>.</li>
  <li><b>Format times using the 12-hour hour and minute, AM or PM, and the time zone.</b> For example, use <em>11:49 AM EST</em>. Capitalize both AM/PM and the time zone.</li>
</ul>
<p>The issue summary should take the following form:</p>
<ul class="mb-3">
  <li>A paragraph briefly explaining when the issue started, what happened, and when normal operations resumed.</li>
  <li>A paragraph explaining necessary background information for readers who may not know the workings of the service and how it broke.</li>
  <li>A paragraph explaining what triggered the issue in the first place.</li>
  <li>A paragraph explaining what the immediate response to the issue to the point to recovery.</li>
  <li>A paragraph explaining what long-term actions were taken to ensure the issue does not occur again.</li>
</ul>
<p>Example given in the above form:</p>
<ul class="list-group mb-3">
  <li class="list-group-item">Starting at 11:49 AM EST on July 11th, 2023, users started reporting increased errors and authentication failures when trying to access Banner. Some other services that depend on Banner also experienced increased error rates as a result, including Etrieve, Slate for Advancement, Slate for Admissions, IBM Cognos, Accomodate, and Advocate. Banner issues were resolved and error rates began to return to normal levels at 1:45 PM EST, and all affected services had fully recovered by 3:37 PM EST.</li>
  <li class="list-group-item">To explain what happened, we need to share some information about how Banner caches data. In order to accelerate database calls, Banner caches some commonly used functions in memory and the schema for what those functions return on the disk. When a request is made to Banner, if it is a cached query, the compute module will query using the stored query in memory and will expect that data to be returned in a form that is stored on the disk.</li>
  <li class="list-group-item">At 10:44 AM EST, a new job was run on the Banner database. Banner began to cache this data as it normally does, but the sheer number of new queries could not be stored in memory. As a result, Banner began using a secondary storage tier on the disk, rather than the primary storage in system memory. This resulted in the system disk filling up much faster than was previously anticipated, in addition to schemata stored on disk. This unprecedented traffic reached a critical threshold which caused Banner to simply reject all new queries, whether they were related to the batch of queries that were initially submitted or unrelated queries, such as authentication requests. Since Banner's system disk was full, it was also unable to write new log files to that same system disk, which resulted in a hung system.</li>
  <li class="list-group-item">The systems and infrastructure teams immediately began diagnosis of the issue. By 12:55 PM EST, infrastructure maintenance had identified the problem and its impact on the rest of the database. As a safeguard step, they immediately began taking snapshots and manually triggered backups (in addition to standard hourly backups) to save as much work as possible. They then executed a rescaling job that increased the amount of memory and disk space to accomodate larger batch jobs. They also were forced to cancel the job that had initially caused the disk issue to arise in the first place. By 1:00 PM EST, new jobs began to run successfully alongside existing jobs that were not cancelled as part of recovry. By 1:22 PM EST, Banner was fully recovered and had successfully processed its backlog of batch jobs that were submitted before the issue took hold. As a result, all adjunct services that rely on Banner data also began to recover. By 3:00 PM EST, our systems team and infrastructure team concluded that all services had returned to normal operations, and all services that depended on Banner were operating normally.</li>
  <li class="list-group-item">We have taken several actions to prevent a recurrence of this event. We immediately enabled checks on new jobs to avoid accepting jobs that would trigger the same issue while we worked on the bug that caused the issue. We also added filler files that could quickly be removed in the event that the disk was full, which would allow the system to continue to function briefly while the root cause was resolved.</li>
</ul>
<p>The service impact's definition is much looser, since it can vary so much between services and incidents. Some services only affect themselves, while others cause a cascading effect. In this section, you should cover all of the affected services and what triggered those elevated errors. On heavily interconnected services (like Banner, Shibboleth, or Duo), this section is important because it helps define what the spread of the outage was. Some service outages are localized, and they don't affect systems outside of themselves, like PaperCut, WCOnline, or ArcGIS. If an outage introduced latency or time before failure in another system, that is also important; for example, integrations from Banner experienced latency of up to 400 seconds before entering a failure state. This section should also be used to define the trickle-back after the root service issue is resolved to the point of full recovery.</p>
