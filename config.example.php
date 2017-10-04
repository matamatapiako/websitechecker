<?php

class app_config
{

//APPLICATION SETTINGS
  const BaseURL = 'http://dev.mpdc.nz/websitechecker/';

// EMAIL SETTINGS
  const EmailFromName = 'Website Checker';
  const EmailFromAddress = 'webmaster@mpdc.govt.nz';
  const EmailCCTo = 'webmaster@mpdc.govt.nz';
  const SMTPHost = 'localhost';
  const SMTPPort = '25';
  const SMTPSecurity = '';
  const SMTPUser = '';
  const SMTPPassword = '';
  const SMTPReturnPath = 'webmaster@mpdc.govt.nz';

// DATABASE SETTINGS
	const DB_HOST = 'localhost';
	const DB_USER = 'root';
	const DB_PASSWORD = 'changeme';
	const DB_NAME = 'websitechecker';

}

?>
