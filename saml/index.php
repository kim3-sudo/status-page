<?php

session_start();

require_once 'php-saml/_toolkit_loader.php';

require_once 'saml_settings.php';

$auth = new OneLogin_Saml2_Auth($settingsInfo);

if (isset($_GET['sso'])) {
  $auth->login();
} else if (isset($_GET['sso2'])) {
  $returnTo = $spBaseUrl.'../admin/admin.php';
  $auth->login($returnTo);
} else if (isset($_GET['slo'])) {
  $returnTo = null;
  $parameters = array();
  $nameId = null;
  $sessionIndex = null;
  $nameIdFormat = null;
  $samlNameIdNameQualifier = null;
  $samlNameIdSPNameQualifier = null;
  if (isset($_SESSION['samlNameId'])) {
    $nameId = $_SESSION['samlNameId'];
  }
  if (isset($_SESSION['samlNameIdFormat'])) {
    $nameIdFormat = $_SESSION['samlNameIdFormat'];
  }
  if (isset($_SESSION['samlNameIdNameQualifier'])) {
    $samlNameIdNameQualifier = $_SESSION['samlNameIdNameQualifier'];
  }
  if (isset($_SESSION['samlNameIdSPNameQualifier'])) {
    $samleNameIdSPNameQualifier = $_SESSION['samlNameIdSPNameQualifier'];
  }
  if (isset($_SESSION['samlSessionIndex'])) {
    $sessionIndex = $_SESSION['samlSessionIndex'];
  }
  $auth->logout($returnTo, $parameters, $nameId, $sessionIndex, false, $nameIdFormat, $samlNameIdQualifier, $samlNameIdSPNameQualifier);
} else if (isset($_GET['acs'])) {
  if (isset($_SESSION) && isset($_SESSION['AuthNRequestID'])) {
    $requestID = $_SESSION['AuthNRequestID'];
  } else {
    $requestID = null;
  }
  $auth->processResponse($requestID);
  $errors = $auth->getErrors();
  if (!empty($errors)) {
    echo '<p>'. implode(', ', $errors). '</p>';
    if ($auth->getSettings()->isDebugActive()) {
      echo '<p>'.htmlentities($auth->getLastErrorReason()).'</p>';
    }
  }
  if (!$auth->isAuthenticated()) {
    echo "<p>Not authenticated</p>";
    exit();
  }

  $_SESSION['samlUserdata'] = $auth->getAttributes();
  $_SESSION['samlNameId'] = $auth->getNameId();
  $_SESSION['samlNameIdFormat'] = $auth->getNameIdFormat();
  $_SESSION['samlNameIdNameQualifier'] = $auth->getNameIdNameQualifier();
  $_SESSION['samlNameIdSPNameQualifier'] = $auth->getNameIdSPNameQualifier();
  $_SESSION['samlSessionIndex'] = $auth->getSessionIndex();
  unset($_SESSION['AuthNRequestID']);
  if (isset($_POST['RelayState']) && OneLogin_Saml2_Utils::getSelfURL() != $_POST['RelayState']) {
    $auth->redirectTo($_POST['RelayState']);
  }
} else if (isset($_GET['sls'])) {
  if (isset($_SESSION) && isset($_SESSION['LogoutRequestID'])) {
    $requestID = $_SESSION['LogoutRequestID'];
  } else {
    $requestID = null;
  }
  $auth->processSLO(false, $requestID);
  $errors = $auth->getErrors();
  if (empty($errors)) {
    echo '<p>Successfully logged out</p>';
  } else {
    echo '<p>',htmlentities(implode(", ", $errors)) , '</p>';
    if ($auth->getSettings()->isDebugActive()) {
      echo '<p>'.htmlentities($auth->getLastErrorReason()).'</p>';
    }
  }
}
if (isset($_SESSION['samlUserData'])) {
  if (!empty($_SESSION['samlUserdata'])) {
    $attributes = $_SESSION['samlUserdata'];
    echo 'Attributes Listing<br>';
    echo '<table><thead><th>Name</th><th>Values</th></thead></tbody>';
    foreach ($attributes as $attributeName => $attributeValues) {
      echo '<tr><td>' . htmlentities($attributeName) . '</td><td><ul>';
      foreach($attributeValues as $attributeValue) {
        echo '<li>' . htmlentities($attributeValue) . '</li>';
      }
      echo '</ul></td></tr>';
    }
    echo '</tbody></table>';
  } else {
    echo '<p>There are no attributes</p>';
  }
  echo '<p><a href="?slo">Logout</a></p>';
} else {
  //echo '<p><a href="?sso">Login</a></p>';
  echo '<p><a href="?sso2">Click here to login via SSO</a></p>';
}
