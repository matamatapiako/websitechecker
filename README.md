Website Checker
===============

Overview
--------

Website Checker is a content review system for websites. A website administration team can assign various web pages across multiple sites to their organisations personel as subscribers. The subscribers will recieve regular reminders to review the content of their webpages and suggest updates should they be required.


Installation
------------

1. Copy contents of this folder into /var/www/html or where ever the root directory of your webserver is

2. Create a new mysql database and user - import mysql.install.sql into your new database

3. Copy the config.example.php file to config.php

4. Open your new config.php and change the database settings to match your setup

5. Change the Base URL and SMTP server settings in config.php and save the file

6. Install the crontab to one of your user accounts that has access to PHP (i.e. using "sudo -u <username> crontab /var/www/html/crontab")

7. Browse to your webserver http://(webserver name or ip)/ and log in as the admin user (username admin, password admin)

8. Add your first site and it's primary domain

9. Start adding webpages

10. Add subscribers and subscribe them to their webpages


