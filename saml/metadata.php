<?php
require_once '../vendor/onelogin/php-saml/_toolkit_loader.php';
require_once 'saml_settings.php';
try {
  $settings = new OneLogin_Saml2_Settings($settingsInfo, true);
  $metadata = $settings->getSPMetadata();
  $errors = $settings->validateMetadata($metadata);
  if (empty($errors)) {
    header('Content-Type: text/xml');
    echo $metadata;
  } else {
    throw new OneLogin_Saml2_Error(
      'Invalid service provider metadata: '.implode(', ', $errors),
      OneLogin_Saml2_Error:METADATA_SP_INVALID
    );
  }
} catch (Exception $e) {
  echo $e->getMessage();
}
