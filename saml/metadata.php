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

// Load SAML toolkit (no HTML output — this endpoint must return XML)
require_once __DIR__ . '/../vendor/onelogin/php-saml/_toolkit_loader.php';
require_once __DIR__ . '/saml_settings.php';

try {
    $settings = new OneLogin_Saml2_Settings($settingsInfo, true);
    $metadata = $settings->getSPMetadata();
    $errors   = $settings->validateMetadata($metadata);
    if (empty($errors)) {
        header('Content-Type: text/xml');
        echo $metadata;
    } else {
        throw new OneLogin_Saml2_Error(
            'Invalid SP metadata: ' . implode(', ', $errors),
            OneLogin_Saml2_Error::METADATA_SP_INVALID
        );
    }
} catch (Exception $e) {
    http_response_code(500);
    echo htmlspecialchars($e->getMessage());
}
