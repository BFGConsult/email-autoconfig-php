<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('display_startup_errors', 'On');
include('MailSetup.inc.php');

$ms=new MailSetup('../.well-known/autoconfig/mail/config-v1.1.xml');
header('Content-Type: application/json');

if (array_key_exists('email', $_GET)) {
  $email = filter_var($_GET["email"], FILTER_SANITIZE_EMAIL);
  if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    $email=NULL;
  }
}
else {
  http_response_code(422);
  $email=NULL;
}
echo json_encode($ms->toDict($email), JSON_PRETTY_PRINT);
?>
