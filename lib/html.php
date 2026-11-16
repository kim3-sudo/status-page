<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Output helpers. Use e() for any value echoed into HTML text or an attribute,
// and safeUrl() for any value used as an href or src.

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Returns the escaped URL when it is an http(s) or mailto link, or a relative
 * path. Returns null for anything else (javascript:, data:, vbscript:, ...), so
 * callers can omit the link.
 */
function safeUrl(?string $url): ?string
{
    $url = trim((string)$url);
    if ($url === '') {
        return null;
    }

    $absolute = preg_match('#^(https?://|mailto:)#i', $url) === 1;
    $relative = $url[0] === '/' || $url[0] === '#';

    return ($absolute || $relative) ? e($url) : null;
}
