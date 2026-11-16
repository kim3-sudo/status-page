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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load SAML library and settings (also establishes $db and $spBaseUrl)
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/saml_settings.php';

use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Utils;

// samlWriteToLog() comes from lib/db_helpers.php. Loading it avoids pulling in
// _header.php, which outputs HTML.
require_once __DIR__ . '/../lib/db_helpers.php';

// Minimal error page — keeps branding out of this handler so XML responses stay clean
function samlErrorPage($title, $message, $backUrl = '../login.php', $statusCode = 401) {
    http_response_code($statusCode);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">'
        . '<title>' . htmlspecialchars($title) . '</title></head>'
        . '<body class="bg-light"><div class="container mt-5"><div class="row justify-content-center">'
        . '<div class="col-md-6"><div class="card shadow-sm"><div class="card-body">'
        . '<h1 class="h4 mb-3">' . htmlspecialchars($title) . '</h1>'
        . '<p>' . $message . '</p>'
        . '<a href="' . htmlspecialchars($backUrl) . '" class="btn btn-primary">Return to login</a>'
        . '</div></div></div></div></div></body></html>';
    exit();
}

$auth = new Auth($settingsInfo);

// ── SSO: initiate login, return to referring page ──────────────────────────
if (isset($_GET['sso'])) {
    samlWriteToLog($db, 'SSO login initiated', -1);
    $auth->login();

// ── SSO2: initiate login, return to admin portal ───────────────────────────
} elseif (isset($_GET['sso2'])) {
    samlWriteToLog($db, 'SSO login initiated (admin portal redirect)', -1);
    $returnTo = rtrim($spBaseUrl, '/') . '/admin/admin.php';
    $auth->login($returnTo);

// ── SLO: initiate Single Logout with the IdP ──────────────────────────────
} elseif (isset($_GET['slo'])) {
    $uid = isset($_SESSION['id']) ? $_SESSION['id'] : -1;
    samlWriteToLog($db, 'SAML SLO initiated', $uid);

    $nameId                    = isset($_SESSION['samlNameId'])                ? $_SESSION['samlNameId']                : null;
    $nameIdFormat              = isset($_SESSION['samlNameIdFormat'])           ? $_SESSION['samlNameIdFormat']           : null;
    $samlNameIdNameQualifier   = isset($_SESSION['samlNameIdNameQualifier'])    ? $_SESSION['samlNameIdNameQualifier']    : null;
    $samlNameIdSPNameQualifier = isset($_SESSION['samlNameIdSPNameQualifier'])  ? $_SESSION['samlNameIdSPNameQualifier']  : null;
    $sessionIndex              = isset($_SESSION['samlSessionIndex'])           ? $_SESSION['samlSessionIndex']           : null;

    $auth->logout(
        null,
        array(),
        $nameId,
        $sessionIndex,
        false,
        $nameIdFormat,
        $samlNameIdNameQualifier,
        $samlNameIdSPNameQualifier
    );

// ── ACS: process Assertion Consumer Service response from IdP ─────────────
} elseif (isset($_GET['acs'])) {
    $requestID = isset($_SESSION['AuthNRequestID']) ? $_SESSION['AuthNRequestID'] : null;
    $auth->processResponse($requestID);

    $errors = $auth->getErrors();
    if (!empty($errors)) {
        samlWriteToLog($db, 'ACS error: ' . implode(', ', $errors), -1, 'WARN');
        $detail = $auth->getSettings()->isDebugActive()
            ? '<br><small class="text-muted">' . htmlspecialchars($auth->getLastErrorReason()) . '</small>'
            : '';
        samlErrorPage(
            'SSO Authentication Error',
            '<strong>' . htmlspecialchars(implode(', ', $errors)) . '</strong>' . $detail
        );
    }

    if (!$auth->isAuthenticated()) {
        samlWriteToLog($db, 'ACS: response not authenticated', -1, 'WARN');
        samlErrorPage('Not Authenticated', 'The SAML response could not be verified. Please try again.');
    }

    // Persist SAML session data for SLO
    $_SESSION['samlUserdata']              = $auth->getAttributes();
    $_SESSION['samlNameId']                = $auth->getNameId();
    $_SESSION['samlNameIdFormat']          = $auth->getNameIdFormat();
    $_SESSION['samlNameIdNameQualifier']   = $auth->getNameIdNameQualifier();
    $_SESSION['samlNameIdSPNameQualifier'] = $auth->getNameIdSPNameQualifier();
    $_SESSION['samlSessionIndex']          = $auth->getSessionIndex();
    unset($_SESSION['AuthNRequestID']);

    // Resolve user email: prefer configured attribute, fall back to NameID
    $emailAttr = getSetting($db, 'saml_email_attribute');
    $samlEmail = null;
    if (!empty($emailAttr) && isset($_SESSION['samlUserdata'][$emailAttr][0])) {
        $samlEmail = $_SESSION['samlUserdata'][$emailAttr][0];
    }
    if (empty($samlEmail)) {
        $samlEmail = $_SESSION['samlNameId'];
    }

    samlWriteToLog($db, 'ACS: authenticated via SSO as ' . $samlEmail, -1);

    // Map SAML identity to an admin user account
    try {
        $user = $db->one(
            'SELECT user_id, user_first_name, user_last_name, user_issuperuser FROM users WHERE user_email = ?',
            [(string)$samlEmail]
        );
    } catch (Throwable $e) {
        error_log('status-page: SAML user lookup failed: ' . $e->getMessage());
        samlWriteToLog($db, 'ACS: DB query failed during user lookup', -1, 'WARN');
        samlErrorPage('Server Error', 'Unable to verify credentials. Please try again later.', '../login.php', 500);
    }

    if ($user !== null) {
        // Rotate the session ID to prevent fixation attacks
        session_regenerate_id(true);

        // Create the same session structure as the password-based login flow
        $_SESSION['loggedin']          = true;
        $_SESSION['email']             = $samlEmail;
        $_SESSION['id']                = $user['user_id'];
        $_SESSION['firstname']         = $user['user_first_name'];
        $_SESSION['lastname']          = $user['user_last_name'];
        $_SESSION['suflag']            = $user['user_issuperuser'];
        $_SESSION['saml_authenticated'] = true;  // flag used by logout.php

        samlWriteToLog($db, 'SSO login successful', $user['user_id']);
    } else {
        samlWriteToLog($db, 'ACS: no admin account for SSO email ' . $samlEmail, -1, 'WARN');
        samlErrorPage(
            'Access Denied',
            'No administrator account is associated with <strong>' . htmlspecialchars($samlEmail) . '</strong>.'
            . ' Contact your administrator to be granted access.',
            '../login.php',
            403
        );
    }

    // Honor the RelayState (e.g. the admin portal URL set by ?sso2), otherwise
    // fall back to the admin portal root.
    if (isset($_POST['RelayState']) && Utils::getSelfURL() != $_POST['RelayState']) {
        $auth->redirectTo($_POST['RelayState']);
    } else {
        header('Location: ' . rtrim($spBaseUrl, '/') . '/admin/admin.php');
        exit();
    }

// ── SLS: process Single Logout Service response from IdP ──────────────────
} elseif (isset($_GET['sls'])) {
    $uid       = isset($_SESSION['id']) ? $_SESSION['id'] : -1;
    $requestID = isset($_SESSION['LogoutRequestID']) ? $_SESSION['LogoutRequestID'] : null;

    $auth->processSLO(false, $requestID);
    $errors = $auth->getErrors();

    if (empty($errors)) {
        samlWriteToLog($db, 'SLO completed successfully', $uid);
        session_destroy();
        header('Location: ' . rtrim($spBaseUrl, '/') . '/login.php');
        exit();
    } else {
        samlWriteToLog($db, 'SLO error: ' . implode(', ', $errors), $uid, 'WARN');
        $detail = $auth->getSettings()->isDebugActive()
            ? '<br><small class="text-muted">' . htmlspecialchars($auth->getLastErrorReason()) . '</small>'
            : '';
        samlErrorPage(
            'Logout Error',
            htmlspecialchars(implode(', ', $errors)) . $detail,
            '../index.php',
            500
        );
    }

// ── No recognized action: send to login ───────────────────────────────────
} else {
    header('Location: ../login.php');
    exit();
}
