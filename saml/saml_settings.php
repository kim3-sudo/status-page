<?php
session_start();
include('../templates/_header.php');
$spBaseUrl = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'service_provider_base_url'"))['setting_value'];
$NameIDFormat = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'name_id_format'"))['setting_value'];
$entityId = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'entity_id'"))['setting_value'];
$singleSignOnService = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'sso_service'"))['setting_value'];
$singleLogoutService = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'slo_service'"))['setting_value'];
$x509cert = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'x509cert'"))['setting_value'];
$settingsInfo = array (
  'sp' => array (
    'entityId' => $spBaseUrl.'/saml/metadata.php',
    'assertionConsumerService' => array (
      'url' => $spBaseUrl.'/saml/index.php?acs',
    ),
    'singleLogoutService' => array (
      'url' => $spBaseUrl.'/saml/index.php?sls',
    ),
    'NameIDFormat' => $NameIDFormat,
  ),
  'idp' => array (
    'entityId' => $entityId,
    'singleSignOnService' => array (
      'url' => $singleSignOnService,
    ),
    'singleLogoutService' => array (
      'url' => $singleLogoutService,
    ),
    'x509cert' => $x509cert,
  ),
);
