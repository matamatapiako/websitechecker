<?php

class page_users {
	
	function page_users($app) {
		$this->app = $app;
	}
	
	function script() {
		
		
	}
	
	function content() {
		
		$app = $this->app;
		
		if (isset($_GET['disablenotifications'])) {
			$html = $this->disable_notifications();
		}
		
		if (isset($_GET['enablenotifications'])) {
			$html = $this->enable_notifications();
		}
		
		if (isset($_GET['resetpass'])) {
			$html = $this->reset_user_password();
		}
		
		if (!isset($html)) {
			$html = $this->show_users_list();
		}
		
		$app->append_content($html);
	}
	
	function show_users_list() {
	
		
		$app = $this->app;
		
		$users = $app->dao->query("select user.* from user");
		
		$users_html = '';
		
		foreach ($users as $u) {
			 
			 $users_html .= '<tr>
			 					<td>' . $u['username'] . '</td>
			 					<td>' . $u['realname'] . '</td>
			 					<td>' . $u['email_address'] . '</td>
			 					<td>' . ($u['notifications'] == 1 ? 'Yes' : 'No') . '</td>
			 					<td>
			 						' . ($u['notifications'] == 1 ? '<a href="?p=users&disablenotifications=' . $u['id'] . '" class="btn"><span>Disable Notifications</span></a>' : '') . '
			 						' . ($u['notifications'] == 0 ? '<a href="?p=users&enablenotifications=' . $u['id'] . '" class="btn"><span>Enable Notifications</span></a>' : '') . '
			 						<a href="?p=users&resetpass=' . $u['id'] . '" class="btn"><span>Reset Password</span></a>
			 					</td>
			 				  </tr>';
		}
		
		$html = '
		
		 <h2>Users</h2>
		 
		 <div style="float: right;">
		 	<a class="btn" href="?p=users"><span>Reload Page</span></a>
		 </div>
		 
		 <h3>Users Overview</h3>
		 
		 <table width="100%">
		 	<thead>
		 		<tr>
		 			<td>Username</td>
		 			<td>Real Name</td>
		 			<td>Email Address</td>
		 			<td>Notifications Enabled</td>
		 			<td></td>
		 		</tr>
		 	</thead>
		 	<tbody>
		 	 ' . $users_html . '
		 	</tbody>
		 </table>
		 		
		';
		
		return $html;
		
	}
	
	function reset_user_password() {
		
		$app = $this->app;
		$id = $_GET['resetpass'];
		
		$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789%$@#^";
		$password = '';
		while (strlen($password) != 6) {
			$password .= substr($chars, rand(0,strlen($chars)-1), 1);
		}
		
		$user = $app->dao->query("select user.* from user where `id`=:ID", "SelectOne", array(":ID"=>$id) );
		
		$app->dao->query("update user set `password`=MD5(:pass) where `id`=:ID", "Update", array(":ID"=>$id, ":pass"=>$password) );
		
		if ($user['email_address'] != "") {
		
			$m = new mailer();
			$to = array("name"=>$user['realname'], "address"=>$user['email_address']);
			$from = array("name"=>"websitechecker@mpdc.govt.nz", "address"=>"webmaster@mpdc.govt.nz");
			$subject = "Website Checker - Password Recovery";
			$names = explode(" ", $user['realname']);
			$body = "Hey " . $names[0] . ",\r\n\r\nYour password was reset by another administrator - your login details are as follows;\r\n\r\n";
			
			$body .= "Username: " . $user['username'] . "\r\n";
			$body .= "Password: " . $password . "\r\n";
			$body .= "\r\n\r\nTo login to the website checker, visit this link http://intapps.mpdc.govt.nz/websitechecker/\r\n\r\n";
			$body .= "\r\n\r\nBye for Now\r\n\r\nWebsite Checker";
			
			$m->create_message($to, $from, $subject, $body, 'text/plain');
			$m->send_message();
			
		}
		
		$html = '
		 <h2>Users</h2>
		 
		 <div style="float: right;">
		 	<a class="btn" href="?p=users"><span>Users Overview</span></a>
		 </div>
		 <h3>Reset Users Password</h3>
		 
		 <p>The password for username "' . $user['username'] . '" has been reset and the new password has been sent to the users email address by email. </p>
		 <div align="center">
		 	<a class="btn" href="?p=users"><span>Return To Users Overview</span></a>
		 </div>
		';
		
		return $html;
		
	}
	
	function disable_notifications() {
		
		
		$app = $this->app;
		$id = $_GET['disablenotifications'];
		
		$user = $app->dao->query("select user.* from user where `id`=:ID", "SelectOne", array(":ID"=>$id) );
		
		$app->dao->query("update user set `notifications`=0 where `id`=:ID", "Update", array(":ID"=>$id) );
		
		$html = '
		 <h2>Users</h2>
		 
		 <div style="float: right;">
		 	<a class="btn" href="?p=users"><span>Users Overview</span></a>
		 </div>
		 <h3>Disable User Notifications</h3>
		 
		 <p>Notifications have been disabled for "' . $user['realname'] . '".</p>
		 <div align="center">
		 	<a class="btn" href="?p=users"><span>Continue</span></a>
		 </div>
		';
		
		return $html;
		
	}
	
	function enable_notifications() {
		
		
		$app = $this->app;
		$id = $_GET['enablenotifications'];
		
		$user = $app->dao->query("select user.* from user where `id`=:ID", "SelectOne", array(":ID"=>$id) );
		
		$app->dao->query("update user set `notifications`=1 where `id`=:ID", "Update", array(":ID"=>$id) );
		
		$html = '
		 <h2>Users</h2>
		 
		 <div style="float: right;">
		 	<a class="btn" href="?p=users"><span>Users Overview</span></a>
		 </div>
		 <h3>Enable User Notifications</h3>
		 
		 <p>Notifications have been enabled for "' . $user['realname'] . '".</p>
		 <div align="center">
		 	<a class="btn" href="?p=users"><span>Continue</span></a>
		 </div>
		';
		
		return $html;
		
	}
	
}

?>