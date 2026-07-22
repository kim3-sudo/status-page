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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load SAML library and settings (also establishes $link and $spBaseUrl)
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/saml_settings.php';

use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Utils;

// samlWriteToLog() comes from _db_helpers.php — avoids pulling in _header.php
// (which outputs HTML) while still sharing one prepared-statement implementation.
require_once __DIR__ . '/../templates/_db_helpers.php';

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
    samlWriteToLog($link, 'SSO login initiated', -1);
    $auth->login();

// ── SSO2: initiate login, return to admin portal ───────────────────────────
} elseif (isset($_GET['sso2'])) {
    samlWriteToLog($link, 'SSO login initiated (admin portal redirect)', -1);
    $returnTo = rtrim($spBaseUrl, '/') . '/admin/admin.php';
    $auth->login($returnTo);

// ── SLO: initiate Single Logout with the IdP ──────────────────────────────
} elseif (isset($_GET['slo'])) {
    $uid = isset($_SESSION['id']) ? $_SESSION['id'] : -1;
    samlWriteToLog($link, 'SAML SLO initiated', $uid);

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
        samlWriteToLog($link, 'ACS error: ' . implode(', ', $errors), -1, 'WARN');
        $detail = $auth->getSettings()->isDebugActive()
            ? '<br><small class="text-muted">' . htmlspecialchars($auth->getLastErrorReason()) . '</small>'
            : '';
        samlErrorPage(
            'SSO Authentication Error',
            '<strong>' . htmlspecialchars(implode(', ', $errors)) . '</strong>' . $detail
        );
    }

    if (!$auth->isAuthenticated()) {
        samlWriteToLog($link, 'ACS: response not authenticated', -1, 'WARN');
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
    $emailAttr = getSetting($link, 'saml_email_attribute');
    $samlEmail = null;
    if (!empty($emailAttr) && isset($_SESSION['samlUserdata'][$emailAttr][0])) {
        $samlEmail = $_SESSION['samlUserdata'][$emailAttr][0];
    }
    if (empty($samlEmail)) {
        $samlEmail = $_SESSION['samlNameId'];
    }

    samlWriteToLog($link, 'ACS: authenticated via SSO as ' . $samlEmail, -1);

    // Map SAML identity to an admin user account
    if ($stmt = $link->prepare(
        'SELECT user_id, user_first_name, user_last_name, user_issuperuser FROM users WHERE user_email = ?'
    )) {
        $stmt->bind_param('s', $samlEmail);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userId, $firstName, $lastName, $suFlag);
            $stmt->fetch();
            $stmt->close();

            // Rotate the session ID to prevent fixation attacks
            session_regenerate_id(true);

            // Create the same session structure as the password-based login flow
            $_SESSION['loggedin']          = true;
            $_SESSION['email']             = $samlEmail;
            $_SESSION['id']                = $userId;
            $_SESSION['firstname']         = $firstName;
            $_SESSION['lastname']          = $lastName;
            $_SESSION['suflag']            = $suFlag;
            $_SESSION['saml_authenticated'] = true;  // flag used by logout.php

            samlWriteToLog($link, 'SSO login successful', $userId);
        } else {
            $stmt->close();
            samlWriteToLog($link, 'ACS: no admin account for SSO email ' . $samlEmail, -1, 'WARN');
            samlErrorPage(
                'Access Denied',
                'No administrator account is associated with <strong>' . htmlspecialchars($samlEmail) . '</strong>.'
                . ' Contact your administrator to be granted access.',
                '../login.php',
                403
            );
        }
    } else {
        samlWriteToLog($link, 'ACS: DB prepare failed during user lookup', -1, 'WARN');
        samlErrorPage('Server Error', 'Unable to verify credentials. Please try again later.', '../login.php', 500);
    }

    // Honour the RelayState (e.g. the admin portal URL set by ?sso2), otherwise
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
        samlWriteToLog($link, 'SLO completed successfully', $uid);
        session_destroy();
        header('Location: ' . rtrim($spBaseUrl, '/') . '/login.php');
        exit();
    } else {
        samlWriteToLog($link, 'SLO error: ' . implode(', ', $errors), $uid, 'WARN');
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

// ── No recognised action: send to login ───────────────────────────────────
} else {
    header('Location: ../login.php');
    exit();
}
