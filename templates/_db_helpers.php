<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Permission is hereby granted, free of charge, to any person obtaining a copy of 
  this software and associated documentation files (the “Software”), to deal in 
  the Software without restriction, including without limitation the rights to 
  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies 
  of the Software, and to permit persons to whom the Software is furnished to do 
  so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in all 
  copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
  SOFTWARE.
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
