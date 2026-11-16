<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Renders incidents for the status page. One markup for the public list and the
// admin-only extras, so the two cannot drift apart.

if (!function_exists('renderIncidentList')) {
  /**
   * @param list<array<string, mixed>> $incidents from incidentsForPage(), in display order
   * @param bool $beyondHorizon true for the admin-only extras dated past the public horizon
   */
  function renderIncidentList(array $incidents, bool $beyondHorizon, DateTimeZone $zone): void {
    foreach ($incidents as $incident) {
      // Resolved incidents show a check; anything else shows an exclamation mark.
      $icon = $incident['status'] === 'RES' ? 'fa-check-circle' : 'fa-exclamation-circle';
      // Dated by when the incident started, in the site's timezone.
      $headingDate = epochToSiteLocal($incident['start'], $zone)->format('M. jS, Y');
?>
          <div class="relative incidents-monthly__item pt-2 pb-3 line">
            <p class="incidents-monthly__item__month text-black dark:text-white group-hover:opacity-100 ">
              <span><?=e($headingDate)?></span>
            </p>
            <div class="incident-details incident-item">
              <div class="flex items-start justify-between">
                <div class="incident-details__header">
                  <span><i class="fa-solid <?=$icon?>"></i></span>
                  <span class="incident-name"><?=e($incident['description'])?></span>
                </div>
              </div>
<?php if ($beyondHorizon): ?>
              <p><small class="text-muted">This element is not visible unless you are logged in because the event is more than the public display period in the future.</small></p>
<?php endif; ?>
              <ul class="incident-details__updates">
<?php
      if ($incident['updates'] === []) {
        echo '<li><p>No updates</p></li>';
      }
      foreach ($incident['updates'] as $index => $update) {
        $when = epochToSiteLocal($update['ts'], $zone);
        $stamp = $when->format('M jS, Y') . ' at ' . $when->format('g:i A');
        $isLatest = $index === 0;
        $statusClass = $isLatest ? 'update-list-item__status--desktop first' : 'update-list-item__status--desktop default stormtrooper';
?>
                <li class="incident-update update-list-item">
                  <div class="update-list-item__inner-wrapper">
                    <div title="<?=e($update['status_name'])?>" class="update-list-item__status <?=$statusClass?>"><?=e($update['status_name'])?><?php if ($beyondHorizon && $isLatest): ?><br><small class="text-muted">This is invisible to non-authenticated users.</small><?php endif; ?></div>
                    <div class="update-list-item__message">
                      <span class="opacity-75 inline updated-list-item__date"><p><?=e($stamp)?></p></span>
                      <div class="inline prose-sm prose dark:prose-invert">
                        <?php renderUpdateContent($update['description']); ?>
                      </div>
                    </div>
                  </div>
                </li>
<?php
      }
?>
              </ul>
            </div>
          </div>
<?php
    }
  }
}
