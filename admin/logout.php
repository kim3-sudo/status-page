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
