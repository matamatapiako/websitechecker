<?php


 class page_domains {
 	
	function page_domains($app) {
		
 		$this->app = $app;
		
	}
	
 	function script() {
 		
 	}
 	
 	function content() {
 	
 		$app = $this->app;
 		
 		if (isset($_GET['action'])) {
 			switch ($_GET['action']) {
 			
 				case 'adddomain' : $this->add_domain(); break;
				case 'viewdomain' : $this->view_domain(); break;
 			    case 'deletedomain' : $this->delete_domain(); break;
				case 'updatewhois' : $this->update_whois(); break;
				case 'updatens' : $this->update_dns(); break;
				
 				default : $this->get_overview(); break;
 				
 			}
 		} else {
 			$this->get_overview();
 		}
 		
 	}
	
	
 	function get_overview() {
 		
 		$app = $this->app;
		
		$domains = $app->dao->query("select d.*, s.`name` AS 'site_name'  from `domains` d LEFT JOIN (`sites` s) ON s.`id` = d.`site_id` order by d.id asc", "SelectAll");
		
		$html = '<h2>Domains</h2>
		<div class="fr">
			<a href="?p=domains&action=adddomain" class="btn"><span>Add New Domain</span></a>
		</div>
				<table width="100%">
					<thead>
						<tr>
							<td>Name</td>
							<td>Site Name</td>
							<td>Last WHOIS Check</td>
							<td>Last NS Check</td>
							<td>Date Registered</td>
							<td>Renew On</td>
							<td></td>
						</tr>
					</thead>
					<tbody>';
					
		foreach ($domains as $d) {
			$html .= '
				<tr>
					<td>' . $d['domain_name'] . '</td>
					<td>' . $d['site_name'] . '</td>
					<td>' . $app->datefmt($d['last_whois_check']) . '</td>
					<td>' . $app->datefmt($d['last_ns_check']) . '</td>
					<td>' . $app->datefmt($this->extract_registered_date($d['whois_data']), false) . '</td>
					<td>' . $app->datefmt($this->extract_renew_date($d['whois_data']), false) . '</td>
					<td>
						<a href="?p=domains&action=viewdomain&id=' . $d['id'] . '" class="btn"><span>View Details</span></a>
						<a href="#" class="btn"><span>Edit</span></a>
						<a href="?p=domains&action=deletedomain&id=' . $d['id'] . '" class="btn"><span>Remove</span></a>
					</td>
				</tr>
			';
		}
		
		$html .= '</tbody>
			</table>';
 		
		$app->append_content($html);
 		
 	}
	
	function view_domain() {
 		
 		$app = $this->app;
 		$domain_id = $_GET['id'];
		$domain = $app->dao->query("SELECT d.*, s.`name` AS 'site_name' from `domains` d INNER JOIN (`sites` s) ON s.`id` = d.`site_id` WHERE d.`id`=:id", "SelectOne", array(":id"=>$domain_id) );
 		
		if ($domain === false || $domain['id'] != $domain_id) {
				
				
			$html = '<h2>Domains - View Domain Name Details</h2>
			
			<div align="center">
				
				<h3>Whoops! We could not find the domain you wanted..</h3>
				<a href="?p=domains" class="btn"><span>Domains Overview</span></a>
				
			</div>
			
			';
			
			$app->append_content($html);
			
			return;
			
		}
		
 		$html = '<h2>Domains - View Domain Name Details</h2>
 		
		<div class="fr">
			<a href="?p=domains" class="btn"><span>Back to Overview</span></a>
		</div>
		
		
		 <table>
		  <tr>
		   <td><strong>Domain Name</strong></td>
		   <td>' . $domain['domain_name'] . '</td>
		  </tr>
		  <tr>
		   <td><strong>Site</strong></td>
		   <td>' . $domain['site_name'] . '</td>
		  </tr>
		  <tr>
		   <td><strong>Last Whois Check</strong></td>
		   <td>' . $app->datefmt($domain['last_whois_check']) . '</td>
		  </tr>
		  <tr>
		   <td><strong>Last DNS Check</strong></td>
		   <td>' . $app->datefmt($domain['last_ns_check']) . '</td>
		  </tr>
		  <tr>
		   <td valign="top"><strong>Whois Data</strong></td>
		   <td>' . base64_decode($domain['whois_data']) . '</td>
		  </tr>
		  <tr>
		   <td></td>
		   <td><a href="?p=domains&action=updatewhois&id=' . $domain['id'] . '" class="btn"><span>Refresh Whois Data</span></a><br /><br /></td>
		  </tr>
		  <tr>
		   <td valign="top"><strong>DNS Data</strong></td>
		   <td>' .  $this->extract_ns_data(base64_decode($domain['ns_data'])) . '</td>
		  </tr>
		  <tr>
		   <td></td>
		   <td><a href="?p=domains&action=updatens&id=' . $domain['id'] . '" class="btn"><span>Refresh NS Data</span></a></td>
		  </tr>
		 </table>
		
 		';
 		
		$app->append_content($html);
 		
 	}
	
	function update_dns() {
		
		$app = $this->app;
		
		if (isset($_GET['id'])) {
			$domain_id = $_GET['id'];
			$domain = $app->dao->query("SELECT d.*, s.`name` AS 'site_name' from `domains` d INNER JOIN (`sites` s) ON s.`id` = d.`site_id` WHERE d.`id`=:id", "SelectOne", array(":id"=>$domain_id) );
			
			$dns = base64_encode( $this->get_dns_data($domain['domain_name']) );
			
			$data = array(
				":id"=>$domain_id,
				":dns"=>$dns
			);
			
			$app->dao->query("UPDATE `domains` set `ns_data`=:dns, `last_ns_check`=now() where `id`=:id", 'Update', $data);
			
		}
		
		header('location: ' . $_SERVER['HTTP_REFERER']);
		die();
	}
	
	function update_whois() {
		
		$app = $this->app;
		
		if (isset($_GET['id'])) {
			$domain_id = $_GET['id'];
			$domain = $app->dao->query("SELECT d.*, s.`name` AS 'site_name' from `domains` d INNER JOIN (`sites` s) ON s.`id` = d.`site_id` WHERE d.`id`=:id", "SelectOne", array(":id"=>$domain_id) );
			
			$whois = base64_encode( $this->get_whois_data($domain['domain_name']) );
			
			$data = array(
				":id"=>$domain_id,
				":whois"=>$whois
			);
			
			$app->dao->query("UPDATE `domains` set `whois_data`=:whois, `last_whois_check`=now() where `id`=:id", 'Update', $data);
			
		}
		
		header('location: ' . $_SERVER['HTTP_REFERER']);
		die();
		
	}
	
	function extract_ns_data($data) {
		
		$string_return = '';
		$data = json_decode($data, true);
		foreach ($data[0] as $key => $value) {
			
			$string_return .= $key . ' = ' . $value . ' <br />';
			
		}
		
		return $string_return;
		
	}
 	
	function add_domain() {
 		
 		$app = $this->app;
 		
 		$error = '';
 		
 		if (isset($_GET['submit']) && $_GET['submit'] == 't') {
 			
 			$site_id = $_POST['site_id'];
 			$domain = $_POST['domain_name'];
 			
			//var_dump($domain);
			
			$dns = base64_encode( $this->get_dns_data($domain) );
			$whois = base64_encode( $this->get_whois_data($domain) );
			
			$domain_data = array(
				":site_id"=>$site_id,
				":domain_name"=>$domain,
				":whois"=>$whois,
				":dns"=>$dns
			);
 					
			$app->dao->query("INSERT INTO `domains` (`site_id`, `domain_name`, `last_whois_check`, `last_ns_check`, `whois_data`, `ns_data` ) VALUES (:site_id, :domain_name, now(), now(), :whois, :dns) ", "Insert", $domain_data);
 					
		 					
			$html = '<h2>Domains - Add Domain Name</h2>
			
			<br /><br />
			<div align="center">
				
				<h3>We added your new domain name successfully...YAY!</h3>
				<p><strong><em>What would you like to do next?</em></strong></p>
			
				<a href="?p=domains" class="btn"><span>Domains Overview</span></a>
				<a href="?p=domains&action=adddomain" class="btn"><span>Add another Domain</span></a>
			</div>';
			
			$app->append_content($html);

			return;
 					
 		} 
 		
		$sites = $app->dao->query("select s.* from `sites` s where s.`enabled`=1");
		
		$site_opts = '';
		foreach ($sites as $s) {
			$site_opts .= '<option value="' . $s['id'] . '">' . $s['name'] . '</option>';
		}
		
 		$html = '<h2>Domains - Add Domain Name</h2>
 		
		<div class="fr">
			<a href="?p=domains" class="btn"><span>Back to Overview</span></a>
		</div>
		
		<form action="?p=domains&action=adddomain&submit=t" method="post">
		
		 ' . ($error != '' ? '<p style="color:red;">' . $error . '</p>' : '') . '
		 
		 <table>
		  <tr>
		   <td>Add web page for which site? </td>
		   <td><select name="site_id">' . $site_opts . '</select></td>
		  </tr>
		  <tr>
		   <td>What is the domain name you want to add?</td>
		   <td><input type="text" name="domain_name" /> <br/ ></td>
		  </tr>
		  <tr>
		   <td></td>
		   <td><input type="submit" value="Add Domain" /></td>
		  </tr>
		 </table>
		</form>
		
 		';
 		
		$app->append_content($html);
 		
 	}
 	
 	function delete_domain() {
 	    
 		$app = $this->app;
 		
 	    if (isset($_GET['id'])) {
 	        
         	$page_data = array(
 				":id"=>$_GET['id'],
 			);
 			
 			$app->dao->query("DELETE FROM `domains` where `id`=:id ", "Delete", $page_data);
 			
	 		$html = '<h2>Domains - Delete Domain Name</h2>
	 		
	 		<br /><br />
			<div align="center">
	 			
	 			<h3>We deleted the domain name successfully!</h3>
	 			<p><strong><em>What would you like to do next?</em></strong></p>
	 		
				<a href="?p=domains" class="btn"><span>Domains Overview</span></a>
				<a href="?p=domains&action=adddomain" class="btn"><span>Add A New Domain Name</span></a>
			</div>';
 			
			$app->append_content($html);

 			return;
         
 	    }
 	    
 		$html = '<h2>Domains - Delete Domain Name</h2>
 		
 		<br /><br />
		<div align="center">
 			
 			<h3>Whoops! We couldn\'t find that domain to delete it..</h3>
 		    <p><strong><em>What would you like to do next?</em></strong></p>
			
			<a href="?p=domains" class="btn"><span>Domains Overview</span></a>
			<a href="?p=domains&action=adddomain" class="btn"><span>Add A New Domain Name</span></a>
			
		</div>';
 		
		$app->append_content($html);

 	}
	
	function get_dns_data($domain) {
		$data = json_encode( dns_get_record ( $domain, DNS_A ) );
		return $data;
	}
	
	function get_whois_data($domain) {
		
		$app = $this->app;
		
		$php_whois_server = 'http://webdev.mpdc.govt.nz/nettools/';
		$php_whois_server_url = $php_whois_server . 'whois.php?domain=' . $domain . '&form=f';
		$result = $app->http->send_request($php_whois_server_url, array());
		
		if ($app->http->test_response($result['headers']) === true) {
			return $result['body'];
		}
		
		return '';
		
	}
	
	function extract_renew_date($whois) {
		
		$renew_on = 'unknown';
		
		$whois_data = base64_decode($whois);
		
		$dom = new DOMDocument;
		$dom->loadHTML($whois_data);
		$pre = $dom->getElementsByTagName('pre');
		
		foreach ($pre as $p) {
			$lines = explode("\n", $p->textContent);
			foreach ($lines as $l) {
				if (strpos($l, "domain_datebilleduntil:") !== false) {
					
					$str = str_replace("domain_datebilleduntil: ", "", $l);
					$renew_on = substr($str, 0, strpos($str, "T"));
					
				}
			}
		}
		
		
		return $renew_on;
		
	}
	
	function extract_registered_date($whois) {
		
		$renew_on = 'unknown';
		
		$whois_data = base64_decode($whois);
		
		$dom = new DOMDocument;
		$dom->loadHTML($whois_data);
		$pre = $dom->getElementsByTagName('pre');
		
		foreach ($pre as $p) {
			$lines = explode("\n", $p->textContent);
			foreach ($lines as $l) {
				if (strpos($l, "domain_dateregistered:") !== false) {
					
					$str = str_replace("domain_dateregistered: ", "", $l);
					$renew_on = substr($str, 0, strpos($str, "T"));
					
				}
			}
		}
		
		
		return $renew_on;
		
	}
	
 }

?>