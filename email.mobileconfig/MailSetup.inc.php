<?php
declare(strict_types=1);
error_reporting(-1);
ini_set('display_errors', 'On');

//These should all be ENUMS, but we have to waith for PHP-8
define('SOCK_SSL', 1);
define('CONTEXT_MS', 2);
define('CONTEXT_APPLE', 3);
define('AUTH_PASSWORD_CLEARTEXT', 4);
define('SOCK_STARTTLS', 5);
define('TYPE_IMAP', 6);
define('TYPE_SMTP', 7);
define('CONTEXT_MOZILLA', 8);

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
      $this->username=(string)$xmlServerObject->xpath("username")[0];
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
    if ($context==CONTEXT_MS) return ($this->socketType)?"on":"off";
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
        elseif ($context == CONTEXT_MS) return "IMAP";
        else return "imap";
      case TYPE_SMTP:
        if ($context == CONTEXT_MS) return "SMTP";
        else return "smtp";
      default:
        return "NULL";
    }
  }
  function getContext($context=NULL) {
      if ($context) {
        if (($context == "MS") || ($context == CONTEXT_MS )) return CONTEXT_MS;
        elseif (($context == "Apple") || ($context == CONTEXT_APPLE ) ) return CONTEXT_APPLE;
        return 0;
      }
      else {
        return $this->context;
      }
  }

  function emailExpand($emailVar, $email) {
    $emaillocalpart=strtok($email, '@');
    $emaildomain=strtok('@');
    $keywords=array('%EMAILLOCALPART%', '%EMAILADDRESS%', '%EMAILDOMAIN%');
    $values=array($emaillocalpart, $email, $emaildomain);
    return str_replace($keywords, $values, $emailVar);
  }
  function getUsername($email=NULL, $context=NULL) {
    if ($email) {
      $context=$this->getContext($context);
      if ($context)
            return $this->emailExpand($this->username, $email);
      else
            return $this->username;
    }
    return $this->username;
  }
  function toDict($email, $context) {
      return ['hostname' => $this->getHostname($context),
              'port' => $this->getPort($context),
              'socketType' => $this->getSocketType($context),
              'username' => $this->getUsername($email,$context),
              'authentication' => $this->getAuthentication($context),
              'type' => $this->getType($context)
             ];
  }
  function toString($email=NULL, $context=NULL) {
      return print_r($this->toDict($email, $context), true);
  }
  function __toString() {
      return $this->toString();
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
  function toDict($email=NULL, $context=NULL) {
      return ['displayName' => $this->getDisplayName(),
              'incomingServer' => $this->incomingServer->toDict($email, $context),
              'outgoingServer' => $this->outgoingServer->toDict($email, $context)
             ];
  }
  function toString($email=NULL, $context=NULL) {
      return print_r($this->toDict($email, $context), true);
  }
  function __toString() {
      return $this->toString();
  }


  function getDisplayName() {return $this->displayName;}
};
?>
