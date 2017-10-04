<?php

class app_config
{

//APPLICATION SETTINGS
  const BaseURL = 'http://yourdomain.com/';

// EMAIL SETTINGS
  const EmailFromName = 'Website Checker';
  const EmailFromAddress = 'webmaster@yourdomain.com';
  const EmailCCTo = 'webmaster@yourdomain.com';
  const SMTPHost = 'localhost';
  const SMTPPort = '25';
  const SMTPSecurity = '';
  const SMTPUser = '';
  const SMTPPassword = '';
  const SMTPReturnPath = 'webmaster@yourdomain.com';

// DATABASE SETTINGS
	const DB_HOST = 'localhost';
	const DB_USER = 'root';
	const DB_PASSWORD = 'changeme';
	const DB_NAME = 'websitechecker';

}

?>
