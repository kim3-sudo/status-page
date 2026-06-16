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
session_start();
if (!isset($_SESSION['id'])) {
    http_response_code(403);
    die('Forbidden');
}
include('../templates/_header.php');
writeToLog($link, 'Logout called for user', $_SESSION['id']);

if (isset($_SESSION['saml_authenticated']) && $_SESSION['saml_authenticated'] === true) {
    // SAML SSO session: hand off to the SAML Single Logout handler.
    // The session must NOT be destroyed here — the SLO handler (saml/index.php?sls)
    // needs the samlNameId / samlSessionIndex / etc. still in the session so it can
    // build the correct LogoutRequest for the IdP. The SLS callback will call
    // session_destroy() after the IdP confirms the logout.
    writeToLog($link, 'SAML SLO initiated for SSO user', $_SESSION['id']);
    echo '<div class="container"><div class="row"><div class="col"><p>Signing you out&hellip;</p></div></div></div>';
    include('../templates/_footer.php');
    header('Location: ../saml/index.php?slo');
    exit();
}

// Standard username/password session logout
writeToLog($link, 'Session ended', $_SESSION['id']);
echo '<div class="container"><div class="row"><div class="col"><p>Logged out</p></div></div></div>';
include('../templates/_footer.php');
session_destroy();
header('Location: ../index.php');
exit();
