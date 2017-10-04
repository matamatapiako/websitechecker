<?PHP
// +------------------------------------------------------------+
// |                  send-it.mpdc.govt.nz                      |
// +------------------------------------------------------------+
// | Last Modified:                  25-Feb-2011                |
// | Description: A file upload application for sending large   |
// | email attachment                                      		|
// +------------------------------------------------------------+
// |       Copyright 2010  Michael David   All Rights Reserved. |
// |                      Send-It Version 1.00                  |
// |           Designed for Matamata-Piako District Council     |
// +------------------------------------------------------------+

require_once('include/swift/swift_required.php');

class mailer {

	var $enabled = true;
	var $transport;
	var $message;
	var $show_errors = false;

	function mailer($debug = 'off') {
		$this->debug = $debug;
		if ($this->debug == 'on') { echo 'mailer {} loading config <br />'; }
		$this->load_config();
	}

	function load_config() {

		$config = array(
		 "transport_type" => "smtp",
		 "smtp_host"=>app_config::SMTPHost,
		 "smtp_port"=>app_config::SMTPPort,
		 "smtp_user"=>app_config::SMTPUser,
		 "smtp_passwd"=>app_config::SMTPPassword,
		 "smtp_security"=>app_config::SMTPSecurity, //empty, tls or ssl
		 "return-path-email"=>app_config::SMTPReturnPath
		);

		try {
			if (isset($config['transport_type'])) {
				switch ($config['transport_type']) {
					case 'smtp' :
					 if (isset($config['smtp_host'], $config['smtp_port'], $config['smtp_user'], $config['smtp_passwd'])) {
						 if (!class_exists("Swift")) { throw new Exception("Swift library not found, not able to send emails"); }
						 else {
							 $this->config = $config;
							 $this->transport = Swift_SmtpTransport::newInstance($config['smtp_host'], $config['smtp_port'])
							  ->setUsername($config['smtp_user'])
							  ->setPassword($config['smtp_passwd'])
							  ->setEncryption($config['smtp_security']);
						 }
					 } else {
						 throw new Exception("Configuration error, not able to send emails");
					 }
					break;
				}
			} else {
				$this->enabled = false;
				throw new Exception("Configuration error, not able to send emails");
			}
		} catch (Exception $e) {
			if ($this->show_errors) { echo "<!--Mailer Error: " . $e->getMessage() . "-->\r\n\r\n"; }
		}

		if ($this->debug == 'on') { echo 'mailer {} config loaded <br />'; }
	}

	function create_message($to, $from, $subject, $content, $content_type = 'text/html') {

		if ($this->debug == 'on') { echo 'mailer {} creating message <br />'; }

		$this->setup_message($content, $content_type);
		$this->message->to = $to;
		$this->message->from = $from;
		$this->message->subject = $subject;

		if ($this->debug == 'on') { echo 'mailer {} message created <br />'; }
	}

	function attach_file($filename) {
		if (!isset($this->message->attachments) && is_array($this->message->attachements)) { $this->message->attachments = array(); }
		$this->message->attachments[] = $filename;
	}

	function set_cc($email) {
		$this->message->cc = $email;
	}

	function set_replyto($email) {
		$this->message->replyto = $email;
	}

	function set_bcc($email) {
		$this->message->bcc = $email;
	}

	function setup_message($content, $content_type) {

		if (!isset($this->message)) { $this->message = new mailer_message; } //Create message object if it doesn't exist
		$this->message->content_type = $content_type;

		$this->message->body = $content;

	}

	function cleanup() {
		unset($this->message);
	}

	function send_message() {

		if ($this->debug == 'on') { echo 'mailer {} sending message <br />'; }

		try {
			if ($this->enabled != true) { throw new Exception("Mailer not enabled, can't send message!"); }
			if (!isset($this->message)) { throw new Exception("No message to send!"); }
			if (isset($this->transport)) {
			 switch ($this->config['transport_type']) {

				case 'smtp' :

					 $swift_mailer = Swift_Mailer::newInstance($this->transport);
					 $swift_msg = Swift_Message::newInstance();
					 $m = $this->message;
					  ///var_dump( $m);
					 $swift_msg->setSubject($m->subject);
					 $swift_msg->setBody($m->body, $m->content_type);

					 if (isset($m->attachments) && is_array($m->attachments)) {
						 foreach ($m->attachments as $a) {
							 if (file_exists($a)) { $swift_msg->attach(Swift_Attachment::fromPath($a)); }
						 }
					 }

					 if (isset($m->cc)) {
						 $swift_msg->setCC($m->cc, $m->cc);
					 }

					 $swift_msg->setFrom($m->from['address'], $m->from['name']);
					 $swift_msg->setSender($m->from['address']);
					 if (isset($this->config['return-path-email'])) { $swift_msg->setReturnPath($this->config['return-path-email']); }
					 else { $swift_msg->setReturnPath($m->from['address']); }
					 $swift_msg->setTo($m->to['address'], $m->to['name']);
					 $result = $swift_mailer->send($swift_msg);

					 if ($result == 0) {
						 if ($this->debug == 'on') { echo 'mailer {} message not sent <br />'; }
						 throw new Exception("Message could not be sent!");

					 } else {
						if ($this->debug == 'on') { echo 'mailer {} message sent <br />'; }
						if ($this->show_errors || true) { echo "<!-- Mailer Notice: " . $result . " message(s) sent! -->\r\n\r\n";  }
						$this->cleanup();
						return true;
					 }

				break;
			 }
			} else {
				if ($this->debug == 'on') { echo 'mailer {} message not sent <br />'; }
				throw new Exception("Transport not configured!");
			}

		} catch (Exception $e) {
			if ($this->debug == 'on') { echo 'mailer {} message not sent - ERROR IN COMMENT <br />'; }
			if ($this->show_errors || true) { echo "<!-- Mailer Error: " . $e->getMessage() . " -->\r\n\r\n"; }
		}

		return false;
	}



}

class mailer_message {

	var $id;
	var $content_type;
	var $to;
	var $from;
	var $subject;
	var $body;
	var $status;
	var $attachements;
	var $cc;
	var $replyto;
	var $bcc;

}

//Run a simple mail test to tdonaldson@mpdc.govt.nz
/*
echo 'Starting Test';

$m = new mailer('on');
$to = array("name"=>"Tim Donaldson", "address"=>"tdonaldson@mpdc.govt.nz");
$from = array("name"=>"Building Online - MPDC", "address"=>"webmaster@mpdc.govt.nz");
$subject = "This is a test email for BOL";
$body = "Hi Tim\r\n\r\n" . $subject . "\r\n\r\nBye for Now\r\n\r\nWebmaster";

$m->create_message($to, $from, $subject, $content, 'text/plain');
$m->send_message();
 *
 */

?>
