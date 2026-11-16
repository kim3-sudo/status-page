<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Time conversion for stored and entered timestamps.
//
// Policy:
//   - Rows that existed before the UTC cutover are site-local (the value of the
//     `timezone` setting at write time). They are read with $isUtc = false.
//   - Rows written after the cutover are stored in UTC and read with $isUtc = true.
//     The flag that records which convention a row uses is added in migration 0.3.0.
//   - Admin input is site-local. It is converted to UTC before it is stored.
//   - Rendering converts back to the site timezone.
//
// Everything here is pure: no clock reads, no database access.

/**
 * Converts a stored DATETIME string to a Unix timestamp.
 * $siteZone is used for rows that are not flagged as UTC.
 */
function storedTimestampToEpoch(string $value, bool $isUtc, DateTimeZone $siteZone): int
{
    $zone = $isUtc ? new DateTimeZone('UTC') : $siteZone;
    return (new DateTimeImmutable($value, $zone))->getTimestamp();
}

/**
 * Converts a site-local datetime (from an admin form) to a UTC 'Y-m-d H:i:s'
 * string for storage. A value with an explicit offset keeps that offset.
 */
function siteLocalToUtcString(string $value, DateTimeZone $siteZone): string
{
    return (new DateTimeImmutable($value, $siteZone))
        ->setTimezone(new DateTimeZone('UTC'))
        ->format('Y-m-d H:i:s');
}

/** Converts a Unix timestamp to the site timezone for display. */
function epochToSiteLocal(int $epoch, DateTimeZone $siteZone): DateTimeImmutable
{
    return (new DateTimeImmutable('@' . $epoch))->setTimezone($siteZone);
}
