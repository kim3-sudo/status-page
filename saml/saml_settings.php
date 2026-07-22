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
