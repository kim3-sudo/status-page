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

// This file is a pure settings loader.
// Do NOT call session_start() or output any HTML here — it is included by
// both the SAML handler (which outputs XML) and the metadata endpoint.
require_once __DIR__ . '/../templates/config.php';
require_once __DIR__ . '/../templates/_db_helpers.php';

$spBaseUrl           = getSetting($link, 'service_provider_base_url');
$NameIDFormat        = getSetting($link, 'name_id_format');
$entityId            = getSetting($link, 'entity_id');
$singleSignOnService = getSetting($link, 'sso_service');
$singleLogoutService = getSetting($link, 'slo_service');
$x509cert            = getSetting($link, 'x509cert');

$settingsInfo = array(
    'sp' => array(
        'entityId' => $spBaseUrl . '/saml/metadata.php',
        'assertionConsumerService' => array(
            'url' => $spBaseUrl . '/saml/index.php?acs',
        ),
        'singleLogoutService' => array(
            'url' => $spBaseUrl . '/saml/index.php?sls',
        ),
        'NameIDFormat' => $NameIDFormat,
    ),
    'idp' => array(
        'entityId' => $entityId,
        'singleSignOnService' => array(
            'url' => $singleSignOnService,
        ),
        'singleLogoutService' => array(
            'url' => $singleLogoutService,
        ),
        'x509cert' => $x509cert,
    ),
);
