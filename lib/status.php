<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Service status codes, their severity order, and how each one is shown.
// Used by the status page (index.php) and the public API (api/service).
// Codes must match the service_status table created by install/run.php.

const SERVICE_STATUS_SEVERITY = [
  'OPE' => 0,
  'PLA' => 1,
  'DEG' => 2,
  'MIN' => 3,
  'MAJ' => 4,
];

// Badge presentation on the status page.
const SERVICE_STATUS_DISPLAY = [
  'OPE' => ['class' => 'text-bg-success', 'icon' => 'fa-check-circle',        'label' => 'Operational',          'tooltip' => 'The service is working normally and as expected.'],
  'DEG' => ['class' => 'text-bg-warning', 'icon' => 'fa-exclamation-circle',   'label' => 'Degraded Performance', 'tooltip' => 'The service may be experiencing degraded performance.'],
  'PLA' => ['class' => 'text-bg-info',    'icon' => 'fa-circle-info',          'label' => 'Planned Maintenance',  'tooltip' => 'There is planned maintenance for this system soon.'],
  'MIN' => ['class' => 'text-bg-warning', 'icon' => 'fa-exclamation-circle',   'label' => 'Minor Outage',         'tooltip' => 'The system may be experiencing a minor outage right now.'],
  'MAJ' => ['class' => 'text-bg-danger',  'icon' => 'fa-triangle-exclamation', 'label' => 'Major Outage',         'tooltip' => 'The system may be experiencing a major outage right now.'],
];

// Wording used in the public API's aggregate response.
const SERVICE_STATUS_SUMMARY = [
  'OPE' => ['brief' => 'Operational',          'description' => 'All systems operational.'],
  'DEG' => ['brief' => 'Degraded',             'description' => 'Some systems are experiencing degraded performance.'],
  'PLA' => ['brief' => 'Planned Maintenance',  'description' => 'Some systems are undergoing planned maintenance.'],
  'MIN' => ['brief' => 'Minor Outage',         'description' => 'Some systems are experiencing minor outages.'],
  'MAJ' => ['brief' => 'Major Outage',         'description' => 'Some systems are experiencing major outages.'],
];

/**
 * The most severe status among $codes. Unknown codes are ignored; an empty
 * list means operational.
 */
function worstServiceStatus(array $codes): string
{
    $worst = 'OPE';
    foreach ($codes as $code) {
        if (isset(SERVICE_STATUS_SEVERITY[$code])
            && SERVICE_STATUS_SEVERITY[$code] > SERVICE_STATUS_SEVERITY[$worst]) {
            $worst = $code;
        }
    }
    return $worst;
}

/** Badge presentation for one code. Unknown codes display as operational. */
function serviceStatusDisplay(?string $code): array
{
    return SERVICE_STATUS_DISPLAY[$code ?? ''] ?? SERVICE_STATUS_DISPLAY['OPE'];
}

/** API wording for one code. Unknown codes are reported as operational. */
function serviceStatusSummary(string $code): array
{
    return SERVICE_STATUS_SUMMARY[$code] ?? SERVICE_STATUS_SUMMARY['OPE'];
}
