<?php
error_reporting(-1);
ini_set('display_errors', 'On');
//declare(strict_types=1);
header('Content-Type: application/xml');
//$emailProvider=$xml['emailProvider']['domain'];
define('SOCK_SSL',1);
define('CONTEXT_MS',2);
define('CONTEXT_APPLE',3);
define('AUTH_PASSWORD_CLEARTEXT',4);
define('SOCK_STARTTLS',5);
define('TYPE_IMAP',6);
define('TYPE_SMTP',7);
class MailServer {
  private $xmlServerObject;
  private $hostname;
  private $socketType;
  private $authentication;
  private $username;
  private $context;
  private $type;
  function __construct($xmlServerObject, $context=NULL) {
      $this->context=$this->getContext($context);
      $this->xmlServerObject=$xmlServerObject;
      $this->setType($this->xmlServerObject->attributes()['type']);
      $this->hostname=(string)$xmlServerObject->xpath("hostname")[0];
      $this->port=(int)$xmlServerObject->xpath("port")[0];
      $this->setSocketType($xmlServerObject->xpath("socketType")[0]);
      $this->setAuthentication($xmlServerObject->xpath("authentication")[0]);
      $this->username=$xmlServerObject->xpath("username")[0];
  }

  function getHostname($context=NULL) {return $this->hostname;}
  function getPort($context=NULL) {return $this->port;}
  function setSocketType($st) {
      if($st=="SSL" or $st==SOCK_SSL)
        $this->socketType=SOCK_SSL;
      elseif($st=="STARTTLS" or $st==SOCK_STARTTLS)
        $this->socketType=SOCK_STARTTLS;
      else
        $this->socketType=0;
  }
  function useSSL($context=NULL) {
    $context=$this->getContext($context);
    if ($context==CONTEXT_APPLE) return ($this->socketType)?"<true/>":"<false/>";
    return $this->socketType!=0;
  }
  function getSocketType($context=NULL) {
    $context=$this->getContext($context);
    switch ($this->socketType) {
      case SOCK_SSL:
        return "SSL";
      case SOCK_STARTTLS:
        return "STARTTLS";
      default:
        return "NULL";
    }
  }
  function setAuthentication($auth) {
      if($auth=="password-cleartext" or $auth==AUTH_PASSWORD_CLEARTEXT)
        $this->authentication=AUTH_PASSWORD_CLEARTEXT;
      else
        $this->authentication=0;
  }
  function getAuthentication($context=NULL) {
    $context=$this->getContext($context);
    switch ($this->authentication) {
      case AUTH_PASSWORD_CLEARTEXT:
        if ($context == CONTEXT_APPLE) return "EmailAuthPassword";
        return "password-cleartext";
      default:
        return "NULL";
    }
  }
  function setType($type) {
      if($type=="imap" or $type==TYPE_IMAP)
        $this->type=TYPE_IMAP;
      elseif($type=="smtp" or $type==TYPE_SMTP)
        $this->type=TYPE_SMTP;
      else
        $this->type=0;
  }
  function getType($context=NULL) {
    $context=$this->getContext($context);
    switch ($this->type) {
      case TYPE_IMAP:
        if ($context == CONTEXT_APPLE) return "EmailTypeIMAP";
        return "imap";
      case TYPE_SMTP:
        return "smtp";
      default:
        return "NULL";
    }
  }
  function getContext($context=NULL) {
      if ($context) {
        if ($context == "MS") return CONTEXT_MS;
        elseif ($context == "Apple") return CONTEXT_APPLE;
        return 0;
      }
      else {
        return $this->context;
      }
  }

  function emailExpand($emailVar, $email) {
    if ($emailVar=="%EMAILLOCALPART%") return strtok($email, '@');
    return $emailVar;
  }
  function getUsername($email=NULL, $context=NULL) {
    if ($email) {
      $context=$this->getContext($context);
      if ($context) {
        switch($context) {
          case CONTEXT_MS:
          case CONTEXT_APPLE:
            return $this->emailExpand($this->username, $email);
          default:
            return $this->username;
        }
      }
    }
    return $this->username;
  }
  function toDict($email, $context) {
      return ['hostname' => $this->getHostname(),
              'port' => $this->getPort(),
              'socketType' => $this->getSocketType(),
              'username' => $this->getUsername($email,$context),
              'authentication' => $this->getAuthentication($context),
              'type' => $this->getType($context)
             ];
  }
  function toString($email, $context) {
      return print_r($this->toDict($email, $context), true);
  }

};

class MailSetup {
  private $emailProvider;
  private $displayName;
  private $incomingServer;
  private $outgoingServer;
  private $context;
  function __construct($filename, $context) {
      $xml=simplexml_load_file($filename);
      //print_r($xml);
      $this->emailProvider=$xml->xpath("/clientConfig/emailProvider")[0];
      $this->displayName=(string)$this->emailProvider->xpath("displayName")[0];
      $this->incomingServer=new MailServer($this->emailProvider->xpath("incomingServer")[0], $context);
      $this->outgoingServer=new MailServer($this->emailProvider->xpath("outgoingServer")[0], $context);
      $this->context=$this->incomingServer->getContext($context);
  }
  function getIncomingServer() {
    return $this->incomingServer;
  }
  function getOutgoingServer() {
    return $this->outgoingServer;
  }
  function toDict($email, $context) {
      return ['displayName' => $this->getDisplayName(),
              'incomingServer' => $this->incomingServer->toDict($email, $context),
              'outgoingServer' => $this->outgoingServer->toDict($email, $context)
             ];
  }
  function toString($email, $context) {
      return print_r($this->toDict($email, $context), true);
  }

  function getDisplayName() {return $this->displayName;}
};

$ms=new MailSetup('config-v1.1.xml', 'Apple');

$email = filter_var($_GET["email"], FILTER_SANITIZE_EMAIL);
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
	<key>PayloadContent</key>
	<array>
		<dict>
			<key>EmailAccountDescription</key>
			<string><?php echo $ms->getDisplayName(); ?></string>
			<key>EmailAccountName</key>
			<string><?php echo $email ?></string>
			<key>EmailAccountType</key>
			<string><?php echo $ms->getIncomingServer()->getType(); ?></string>
			<key>EmailAddress</key>
			<string><?php echo $email ?></string>
			<key>IncomingMailServerAuthentication</key>
			<string><?php echo $ms->getIncomingServer()->getAuthentication(); ?></string>
			<key>IncomingMailServerHostName</key>
			<string><?php echo $ms->getIncomingServer()->getHostname(); ?></string>
			<key>IncomingMailServerPortNumber</key>
			<integer><?php echo $ms->getIncomingServer()->getPort(); ?></integer>
			<key>IncomingMailServerUseSSL</key>
			<?php echo $ms->getIncomingServer()->useSSL(); ?>

			<key>IncomingMailServerUsername</key>
			<string><?php echo $ms->getIncomingServer()->getUsername($email); ?></string>
			<key>OutgoingMailServerAuthentication</key>
			<string><?php echo $ms->getOutgoingServer()->getAuthentication(); ?></string>
			<key>OutgoingMailServerHostName</key>
			<string><?php echo $ms->getOutgoingServer()->getHostname(); ?></string>
			<key>OutgoingMailServerPortNumber</key>
			<integer><?php echo $ms->getOutgoingServer()->getPort(); ?></integer>
			<key>OutgoingMailServerUseSSL</key>
			<?php echo $ms->getOutgoingServer()->useSSL(); ?>

			<key>OutgoingMailServerUsername</key>
			<string><?php echo $ms->getIncomingServer()->getUsername($email); ?></string>
			<key>OutgoingPasswordSameAsIncomingPassword</key>
			<true/> <?php /*FIX */ ?>
			<key>PayloadDescription</key>
			<string>Email autoconfiguration profile</string>
			<key>PayloadDisplayName</key>
			<string><?php echo $email ?> - Email</string>
			<key>PayloadIdentifier</key>
			<string>org.example.autoconfig</string>
			<key>PayloadType</key>
			<string>com.apple.mail.managed</string>
			<key>PayloadUUID</key>
			<string>54ea0cab-0526-4909-8ff1-b3908dc8eee8</string>
			<key>PayloadVersion</key>
			<real>1</real>
			<key>SMIMEEnablePerMessageSwitch</key>
			<false/>
			<key>SMIMEEnabled</key>
			<false/>
			<key>disableMailRecentsSyncing</key>
			<false/>
		</dict>
	</array>
	<key>PayloadDescription</key>
        <string>Sample email autoconfiguration</string>
        <key>PayloadDisplayName</key>
        <string><?php echo $email ?> - Email</string>
        <key>PayloadIdentifier</key>
        <string>org.example.autoconfig</string>
	<key>PayloadOrganization</key>
        <string><?php echo $ms->getDisplayName(); //FIX ?></string>
	<key>PayloadRemovalDisallowed</key>
	<false/>
	<key>PayloadType</key>
	<string>Configuration</string>
	<key>PayloadUUID</key>
	<string>54ea0cab-0526-4909-8ff1-b3908dc8eee8</string>
	<key>PayloadVersion</key>
	<integer>1</integer>
</dict>
</plist>
