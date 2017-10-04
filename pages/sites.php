<?php


 class page_sites {
 	
	function page_sites($app) {
		
 		$this->app = $app;
		
	}
	
 	function script() {
 		
 	}
 	
 	function content() {
 	
 		$app = $this->app;
 		
 		if (isset($_GET['action'])) {
 			switch ($_GET['action']) {
 			
 				case 'addsite' : $this->add_site(); break;
				
 				default : $this->get_overview(); break;
 				
 			}
 		} else {
 			$this->get_overview();
 		}
 		
 	}
	
	
 	function get_overview() {
		
 		$app = $this->app;
		
		$sites = $app->dao->query("select s.* from `sites` s order by s.id asc", "SelectAll");
		
		$html = '<h2>Sites</h2>
		<div class="fr">
			<a href="?p=sites&action=addsite" class="btn"><span>Add New Site</span></a>
		</div>
				<table width="100%">
					<thead>
						<tr>
							<td>Name</td>
							<td>Description</td>
							<td>No. of Domains</td>
							<td>No. of Pages</td>
							<td>Enabled</td>
							<td></td>
						</tr>
					</thead>
					<tbody>';
					
		foreach ($sites as $s) {
			$html .= '
				<tr>
					<td>' . $s['name'] . '</td>
					<td>' . $s['description'] . '</td>
					<td>' . $this->domains_count($s['id']) . '</td>
					<td>' . $this->webpages_count($s['id']) . '</td>
					<td>' . ($s['enabled'] == 1 ? 'Yes' : 'No') . '</td>
					<td><a href="#" class="btn"><span>Manage</span></a></td>
				</tr>
			';
		}
		
		$html .= '</tbody>
			</table>';
 		
		$app->append_content($html);
 		
 	}
	
	function add_site() {
 		
 		$app = $this->app;
 		
 		$error = '';
 		
 		if (isset($_GET['submit']) && $_GET['submit'] == 't') {
 			
			
			$site_data = array(
				":name"=>$_POST['name'],
				":desc"=>$_POST['description']
			);
			
			$app->dao->query("INSERT INTO `sites` (`name`, `primary_domain`, `description`, `enabled` ) VALUES (:name, 0, :desc, 1) ", "Insert", $site_data);
			
 			$site_id = $app->dao->insert_id();
 			$domain = $_POST['domain_name'];
 			
			$dns = base64_encode( $this->get_dns_data($domain) );
			$whois = base64_encode( $this->get_whois_data($domain) );
			
			$domain_data = array(
				":site_id"=>$site_id,
				":domain_name"=>$domain,
				":whois"=>$whois,
				":dns"=>$dns
			);
 					
			$app->dao->query("INSERT INTO `domains` (`site_id`, `domain_name`, `last_whois_check`, `last_ns_check`, `whois_data`, `ns_data` ) VALUES (:site_id, :domain_name, now(), now(), :whois, :dns) ", "Insert", $domain_data);
 					
			$domain_id = $app->dao->insert_id();
			
			$site_data = array(
				":id"=>$site_id,
				":domain_id"=>$domain_id
			);
			
			$app->dao->query("UPDATE `sites` set `primary_domain`=:domain_id where `id`=:id ", "Update", $site_data);
					 					
			$html = '<h2>Sites - Add New Site</h2>
			
			<br /><br />
			<div align="center">
				
				<h3>We added your new site successfully...YAY!</h3>
				<p><strong><em>What would you like to do next?</em></strong></p>
			
				<a href="?p=sites" class="btn"><span>Sites Overview</span></a>
				<a href="?p=sites&action=addsite" class="btn"><span>Add another Site</span></a>
			</div>';
			
			$app->append_content($html);

			return;
 					
 		} 
 				
 		$html = '<h2>Sites - Add New Site</h2>
 		
		<div class="fr">
			<a href="?p=sites" class="btn"><span>Back to Overview</span></a>
		</div>
		
		<form action="?p=sites&action=addsite&submit=t" method="post">
		
		 ' . ($error != '' ? '<p style="color:red;">' . $error . '</p>' : '') . '
		 
		 <table>
		  <tr>
		   <td>What do you want to call this site? </td>
		   <td><input type="text" name="name" /> </td>
		  </tr>
		  <tr>
		   <td>Describe what this site for?</td>
		   <td><textarea rows="4" cols="60" name="description"></textarea></td>
		  </tr>
		  </tr>
		  <tr>
		   <td>What is primary domain name for this site?</td>
		   <td><input type="text" name="domain_name" /> <br/ ></td>
		  </tr>
		  <tr>
		   <td></td>
		   <td><input type="submit" value="Add Site" /></td>
		  </tr>
		 </table>
		</form>
		
 		';
 		
		$app->append_content($html);
 		
 	}
	
	function domains_count($site_id) {
		
 		$app = $this->app;
		$count = $app->dao->query("select count(`id`) as 'result' from `domains` d where d.`site_id`=:id", "SelectOne", array(":id"=>$site_id));
		return $count['result'];
		
	}
	
	function webpages_count($site_id) {
		
 		$app = $this->app;
		$count = $app->dao->query("select count(`id`) as 'result' from `webpages` w where w.`site_id`=:id", "SelectOne", array(":id"=>$site_id));
		return $count['result'];
		
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
	
 }
	

?>