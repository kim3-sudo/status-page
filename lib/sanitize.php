<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Incident update bodies: sanitized on write, and again on display so that rows
// written before this check existed are also safe.
//
// Plain text is escaped and split into paragraphs. Anything with markup is run
// through HTML Purifier with a short allowlist of tags, and only http, https and
// mailto links are kept.

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/html.php';

/** The markup allowed in update bodies: what the admin editor produces. */
const UPDATE_ALLOWED_HTML = 'p,br,strong,em,u,s,a[href|rel],ol,ul,li,blockquote,code,pre,h2,h3';

function updatePurifier(): HTMLPurifier
{
    static $purifier = null;
    if ($purifier === null) {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', UPDATE_ALLOWED_HTML);
        $config->set('HTML.Nofollow', true);
        $config->set('HTML.TargetBlank', false);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $config->set('Cache.DefinitionImpl', null);
        $purifier = new HTMLPurifier($config);
    }
    return $purifier;
}

/**
 * Returns safe HTML for an update body. Empty input gives an empty string.
 *
 * Text is treated as HTML only when it contains one of the allowed tags. Anything
 * else is plain text, even if it has angle brackets, so "<line>" stays readable and
 * "<script>" stays inert.
 */
function sanitizeUpdateBody(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }

    $allowedTags = implode('|', ['p', 'br', 'strong', 'em', 'u', 's', 'a', 'ol', 'ul', 'li', 'blockquote', 'code', 'pre', 'h2', 'h3']);
    if (preg_match('/<\s*\/?\s*(' . $allowedTags . ')(?![a-z0-9])[^>]*>/i', $raw) !== 1) {
        $lines = array_filter(array_map('trim', preg_split('/\R/', $raw)), fn (string $line): bool => $line !== '');
        return implode('', array_map(fn (string $line): string => '<p>' . e($line) . '</p>', $lines));
    }

    return updatePurifier()->purify($raw);
}
