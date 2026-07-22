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

// No HTML output and no other side effects here — this file is included by
// contexts that must not emit HTML or do unrelated DB work (install/run.php,
// saml/index.php), in addition to the normal HTML-emitting templates/_header.php.

if (!function_exists('writeToLog')) {
  function writeToLog($link, $entry, $uid, $type = 'INFO') {
    $entry = substr($entry, 0, 139);
    $stmt = $link->prepare('INSERT INTO log (log_entry, log_user_id, log_type) VALUES (?, ?, ?)');
    $stmt->bind_param('sis', $entry, $uid, $type);
    if (!$stmt->execute()) {
      die('Unable to write to log! Auditability violated.');
    }
    $stmt->close();
  }
}

if (!function_exists('samlWriteToLog')) {
  // Same as writeToLog(), but tolerates a missing $link and swallows failures
  // instead of dying — losing an audit log entry mid-SSO-handshake shouldn't
  // break the handshake itself.
  function samlWriteToLog($link, $entry, $uid, $type = 'INFO') {
    if (!$link) {
      return;
    }
    $entry = substr($entry, 0, 139);
    $stmt = $link->prepare('INSERT INTO log (log_entry, log_user_id, log_type) VALUES (?, ?, ?)');
    if ($stmt === false) {
      return;
    }
    $stmt->bind_param('sis', $entry, $uid, $type);
    $stmt->execute();
    $stmt->close();
  }
}

if (!function_exists('getSetting')) {
  function getSetting($link, $key) {
    $stmt = $link->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row['setting_value'] ?? '';
  }
}
