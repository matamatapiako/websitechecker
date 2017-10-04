<?php

class authentication {
	
	var $authenticated = false;
	var $user = null;
	
	function authentication($app=null) {
		
		if (isset($app)) { $this->app = $app; }
		
		$this->login_logout();
		
		if (isset($_SESSION['kc_authenticated'])) {
			$this->authenticated = true;
			$this->user = $_SESSION['kc_authenticated'];
		}
		
	}
	
	function get_login_form() {
		
		$html = '
		
		<h2>Please login to continue</h2>
		<br />
		<form action="index.php" method="post">
			<table>
			 <tr>
			  <td><b>Username:</b></td>
			  <td><input type="text" name="username" /></td>
			 </tr>
			 <tr>
			  <td><b>Password:</b></td>
			  <td><input type="password" name="password" /></td>
			 </tr>
			 <tr>
			  <td></td>
			  <td><input type="submit" value="Login" /></td>
			 </tr>
			</table>
		</form>
		
		';
		
		return $html;
		
	}
	
	function content() {
		
		$this->update_password();
		
	}
	
	function update_password() {
		
		
		if (isset($_GET['a']) && $_GET['a'] == 'updatepass') {
			
			$app = $this->app;
			
			$user = $app->dao->query("Select * from `user` where `id`=:ID", "SelectOne", array(":ID"=>$_SESSION['kc_authenticated']['id']));
			
			if (isset($_POST['submit'])) {
				
				if (isset($_POST['old_password'],$_POST['new_password'],$_POST['confirm_password'])) {
					
					if ($_POST['confirm_password'] != $_POST['new_password']) {
						$error_msg = "New passwords do not match - please re-enter your new passwords again.";
						unset($_POST['confirm_password']);
					}
					
					if (md5($_POST['old_password']) != $user['password']) {
						$error_msg = "Your old password is incorrect, please check and try again.";
						unset($_POST['old_password']);
					}
					
					if (!isset($error_msg)) {
						
						$password = $_POST['new_password'];
						$app->dao->query("update user set `password`=MD5(:pass) where `id`=:ID", "Update", array(":ID"=>$user['id'], ":pass"=>$password) );
						$html .= '<h2>Update My Password</h2>
							<br /><br />
							<p>Your password has been updated succesfully.</p>
						';
						
					}
					
					
				}
				
			}
			
			if (!isset($html)) {
				
				$html = '<h2>Update My Password</h2>
				
				<br /><br />
				' . (isset($error_msg) ? '<p style="color: red;">' . $error_msg . '</p>' : '') . '
				<form action="?a=updatepass" method="post">
				 <input type="hidden" name="submit" value="1" />
				 <table>
				  <tr>
				   <td>Old Password: </td>
				   <td><input type="password" name="old_password" ' . (isset($_POST['old_password']) ? 'value="' . $_POST['old_password'] . '"' : '') . ' /></td>
				  </tr>
				  <tr>
				   <td>New Password: </td>
				   <td><input type="password" name="new_password" ' . (isset($_POST['new_password']) ? 'value="' . $_POST['new_password'] . '"' : '') . ' /></td>
				  </tr>
				  <tr>
				   <td>Confirm New Password: </td>
				   <td><input type="password" name="confirm_password" ' . (isset($_POST['confirm_password']) ? 'value="' . $_POST['confirm_password'] . '"' : '') . ' /></td>
				  </tr>
				  <tr>
				   <td></td>
				   <td><input type="submit" value="Change Password" /></td>
				  </tr>
				 </table>
				</form>';
			
			}
			
			$app->append_content($html);
			$app->end_page();
	
			
		}
		
	}
	
	function login_logout() {
		
		if (isset($_POST['username'])) {
			
			$app = $this->app;
			
			$user = $app->dao->query("Select * from `user` where `username`=:USER", "SelectOne", array(":USER"=>$_POST['username']));
			
			if (isset($user['id']) && $user['id'] > 0) {
				
				if ( md5($_POST['password']) == $user['password'] ) {
					
					$this->authenticated = true;
					$this->user = $user;
					$_SESSION['kc_authenticated'] = $user;
					
				}
				
			}
			
		}
		
		if (isset($_GET['a']) && $_GET['a'] == 'logout') {
			
			unset($_SESSION['kc_authenticated']);
			header('location: index.php');
			die();
			
		}
	}
	
}

$this->auth = new authentication($this);

?>