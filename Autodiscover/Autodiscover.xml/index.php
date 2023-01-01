<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('display_startup_errors', 'On');
include('../../email.mobileconfig/MailSetup.inc.php');

$ms=new MailSetup('../../.well-known/autoconfig/mail/config-v1.1.xml', 'MS');

$raw = file_get_contents('php://input');
$matches = array();
preg_match('/<EMailAddress>(.*)<\/EMailAddress>/', $raw, $matches);
header('Content-Type: application/xml');

if ($matches) {
  $email=$matches[1];
}
elseif (array_key_exists('email', $_GET)) {
  $email = filter_var($_GET["email"], FILTER_SANITIZE_EMAIL);
}
else {
  http_response_code(422);
  $email=NULL;
}
?>
<Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006">
  <Response xmlns="http://schemas.microsoft.com/exchange/autodiscover/outlook/responseschema/2006a">
    <User>
      <DisplayName><?php echo $ms->getDisplayName() ?></DisplayName>
    </User>
    <Account>
      <AccountType>email</AccountType>
      <Action>settings</Action>
      <Protocol>
        <Type><?php echo $ms->getIncomingServer()->getType() ?></Type>
        <Server><?php echo $ms->getIncomingServer()->getHostname() ?></Server>
        <Port><?php echo $ms->getIncomingServer()->getPort() ?></Port>
        <DomainRequired>off</DomainRequired>
        <SPA>off</SPA>
        <SSL><?php echo $ms->getIncomingServer()->useSSL() ?></SSL>
        <AuthRequired>on</AuthRequired>
        <LoginName><?php echo $ms->getIncomingServer()->getUsername($email) ?></LoginName>
      </Protocol>
      <Protocol>
        <Type><?php echo $ms->getOutgoingServer()->getType() ?></Type>
        <Server><?php echo $ms->getOutgoingServer()->getHostname() ?></Server>
        <Port><?php echo $ms->getOutgoingServer()->getPort() ?></Port>
        <DomainRequired>off</DomainRequired>
        <SPA>off</SPA>
        <SSL><?php echo $ms->getOutgoingServer()->useSSL() ?></SSL>
        <AuthRequired>on</AuthRequired>
        <LoginName><?php echo $ms->getOutgoingServer()->getUsername($email) ?></LoginName>
      </Protocol>
    </Account>
  </Response>
</Autodiscover>
