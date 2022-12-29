<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('display_startup_errors', 'On');
include('MailSetup.inc.php');

$ms=new MailSetup('../.well-known/autoconfig/mail/config-v1.1.xml', 'Apple');
header('Content-Type: application/xml');

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
