<?php


 class page_pages {

	function page_pages($app) {

 		$this->app = $app;

	}

 	function script() {

 	}

 	function content() {

 		$app = $this->app;

 		if (isset($_GET['action'])) {
 			switch ($_GET['action']) {

 				case 'addwebpage' : $this->add_webpage(); break;
				case 'viewpage' : $this->view_webpage(); break;
				case 'editpage' : $this->edit_webpage(); break;
			  case 'deletewebpage' : $this->delete_webpage(); break;
				case 'reloadcontent' : $this->reload_page_content(true, null); break;
 				default : $this->get_overview(); break;

 			}
 		} else {
 			$this->get_overview();
 		}

 	}

 	function get_overview() {

 		$app = $this->app;

		//Load posted variables into session for filtering

		if (isset($_POST['filter_site'])) {
			$_SESSION['webpages_filter_site'] = $_POST['filter_site'];
		}

		if (isset($_POST['filter_domain'])) {
			$_SESSION['webpages_filter_domain'] = $_POST['filter_domain'];
		}

		//Load session variables for filtering into local run-time space

		if (isset($_SESSION['webpages_filter_site'])) {
			$filter_site = $_SESSION['webpages_filter_site'];
		}

		if (isset($_SESSION['webpages_filter_domain'])) {
			$filter_domain = $_SESSION['webpages_filter_domain'];
		}

		$joins = "LEFT JOIN (`sites` s) ON s.`id` = w.`site_id` LEFT JOIN (`domains` d) ON w.`domain_id` = d.`id` ";
		$order_by = "order by `title` asc";

		$sql = "select w.*, d.`domain_name`, s.`name` AS 'site_name'  from `webpages` w " . $joins;
		$qry_data = array();

		if (isset($filter_site) && $filter_site != "") {

		  $sql .= "where s.`id` = :sid ";
		  $qry_data = array( ":sid"=>$filter_site );

		  if (isset($filter_domain) && $filter_domain != "") {

		  	$sql .= "AND d.`id` = :domid ";
		  	$qry_data[':domid'] = $filter_domain;

		  }

		}

		$sql .= $order_by;

		$pages = $app->dao->query($sql, "SelectAll", $qry_data);

		$sites = $app->dao->query("select s.* from `sites` s where s.`enabled`=1");

		$site_opts = '<option value=""> - All Sites - </option>';

		if (isset($filter_site) && $filter_site != "") {

			$domains = $app->dao->query("select d.* from `domains` d where d.`site_id`=:site_id", "SelectAll", array(":site_id"=>$filter_site));
			$domain_opts = '<option value=""> - All Domains - </option>';

			foreach ($domains as $d) {
				if (isset($filter_domain) && $filter_domain == $d['id']) { $domain_opts .= '<option selected value="' . $d['id'] . '">' . $d['domain_name'] . '</option>'; }
				else { $domain_opts .= '<option value="' . $d['id'] . '">' . $d['domain_name'] . '</option>'; }
			}

		} else {
			$domain_opts = '<option value=""> - Please Select A Site First - </option>';
		}

		foreach ($sites as $s) {
			if (isset($filter_site) && $filter_site == $s['id']) { $site_opts .= '<option selected value="' . $s['id'] . '">' . $s['name'] . '</option>'; }
			else { $site_opts .= '<option value="' . $s['id'] . '">' . $s['name'] . '</option>'; }
		}


		$html = '<h2>Webpages</h2>
		<div class="fl">
		 <form action="?p=pages" method="post">
		  <select name="filter_site">
		   ' . $site_opts . '
		  </select>
		  <select name="filter_domain">
		   ' . $domain_opts . '
		  </select>
		  <input type="submit" value="Apply" />
		 </form>
		</div>
		<div class="fr">
			<a href="?p=pages&action=addwebpage" class="btn"><span>Add New Webpage</span></a>
		</div>
				<table width="100%">
					<thead>
						<tr>
							<td>Title</td>
							<td>Domain</td>
							<td>URL</td>
							<td>Last Checked</td>
							<td>Latest Ret-Code</td>
							<td>Last Changed</td>
							<td></td>
						</tr>
					</thead>
					<tbody>';

		foreach ($pages as $p) {
			$html .= '
				<tr>
					<td><span title="' . $p['title'] . '">' . (strlen($p['title']) < 50 ? $p['title'] : substr($p['title'],0,10) . '...' . substr($p['title'],-5,10)) . '</span></td>
					<td><span title="Site - ' . $p['site_name'] . '">' . $p['domain_name'] . '</span></td>
					<td><span title="' . $p['page_url'] . '">' . (strlen($p['page_url']) < 40 ? $p['page_url'] : substr($p['page_url'],0,15) . '...' . substr($p['page_url'],-15,15)) . '</span></td>
					<td>' . $app->datefmt($p['last_checked']) . '</td>
					<td>' . $p['latest_response_code'] . '</td>
					<td>' . $app->datefmt($p['last_changed']) . '</td>
					<td>
						<a href="?p=pages&action=viewpage&id=' . $p['id'] . '" class="btn"><span>View Details</span></a>
						<a href="?p=pages&action=editpage&id=' . $p['id'] . '" class="btn"><span>Edit</span></a>
						<a href="?p=pages&action=deletewebpage&id=' . $p['id'] . '" class="btn"><span>Delete</span></a>
					</td>
				</tr>
			';
		}

		$html .= '</tbody>
			</table>';

		$app->append_content($html);

 	}

 	function add_webpage() {

 		$app = $this->app;

 		$error = '';

 		if (isset($_GET['submit']) && $_GET['submit'] == 't') {

 			$site_id = $_POST['site_id'];

 			$domains = $app->dao->query("select d.* from `domains` d where d.`site_id`=:site_id", "SelectAll", array(":site_id"=>$site_id));

 			$page_url = $_POST['page_url'];

 			foreach ($domains as $d) {
 				if (strpos($page_url, $d['domain_name']) !== false) {
 					$domain_id = $d['id'];
 					$domain_name = $d['domain_name'];
 				}
 			}

 			//Check if this URL is actually a part of this website
 			if (!isset($domain_id)) {
 				$error = 'This web url (' . substr($page_url, 0, 25) . '..) can not be matched against any of the sites domains..';
 			} else {

 				$result = $app->http->send_request($page_url, array());
 				if ($app->http->test_response($result['headers']) === true) {

 					$title = $_POST['title'];
 					if ($title == '') {
 						$title = $this->find_page_url_title($result['body']);
 					}

 					$hash = md5($result['body']);

 					$page_url = str_replace($domain_name, "", $page_url);

 					$end_protocol = strpos($page_url, "://");

 					if ($end_protocol !== false) { //only trim protocol if it's actually in the URL
 						$end_protocol += 3;
 						$page_url = substr($page_url, $end_protocol, strlen($page_url)-$end_protocol);
 					}

 					$page_data = array(
 						":site_id"=>$site_id,
 						":domain_id"=>$domain_id,
 						":title"=>$title,
 						":url"=>$page_url,
 						":hash"=>$hash
 					);

 					$app->dao->query("INSERT INTO `webpages` (`site_id`,`domain_id`,`title`,`page_url`,`last_checked`,`current_hash`) VALUES (:site_id, :domain_id, :title, :url, now(), :hash) ", "Insert", $page_data);

 					$page_id = $app->dao->insert_id();

 					$this->reload_page_content(false, $page_id);

			 		$html = '<h2>Webpages - Add Webpage</h2>

			 		<br /><br />
					<div align="center">

			 			<h3>We added your new web page successfully...YAY!</h3>
			 			<p><strong><em>What would you like to do next?</em></strong></p>

						<a href="?p=pages" class="btn"><span>Webpages Overview</span></a>
						<a href="?p=pages&action=addwebpage" class="btn"><span>Add another Webpage</span></a>
					</div>';

					$app->append_content($html);

 					return;

 				} else {
 					$error = 'Failed to connect and/or retrieve content from server..';
 				}

 			}
 		}

		$sites = $app->dao->query("select s.* from `sites` s where s.`enabled`=1");

		$site_opts = '';
		foreach ($sites as $s) {
			$site_opts .= '<option value="' . $s['id'] . '">' . $s['name'] . '</option>';
		}

 		$html = '<h2>Webpages - Add Webpage</h2>

		<div class="fr">
			<a href="?p=pages" class="btn"><span>Back to Overview</span></a>
		</div>

		<form action="?p=pages&action=addwebpage&submit=t" method="post">

		 ' . ($error != '' ? '<p style="color:red;">' . $error . '</p>' : '') . '

		 <table>
		  <tr>
		   <td>Add web page for which site? </td>
		   <td><select name="site_id">' . $site_opts . '</select></td>
		  </tr>
		  <tr>
		   <td>What is the URL for this page?</td>
		   <td><input type="text" name="page_url" /> <br/ ><em>hint - Copy and paste the URL into the box above</em></td>
		  </tr>
		  <tr>
		   <td>What is the pages title?</td>
		   <td><input type="text" name="title" /> <br /><em>hint - if left blank, we will try get the title automatically from the content</em></td>
		  </tr>
		  <tr>
		   <td></td>
		   <td><input type="submit" value="Add Web Page" /></td>
		  </tr>
		 </table>
		</form>

 		';

		$app->append_content($html);

 	}

	function view_webpage() {

		$app = $this->app;

		$page_id = $_GET['id'];
		$sql = "SELECT w.*, d.`domain_name`, s.`name` as 'site_name' from `webpages` w LEFT JOIN (`sites` s ) ON s.`id` = w.`site_id` LEFT JOIN (`domains` d) ON d.`id` = w.`domain_id` where w.`id`=:id";
		$page = $app->dao->query($sql,'SelectOne', array(":id"=>$page_id));


		$server_address = "http://" . $_SERVER['SERVER_NAME'] . str_replace ($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME'])) . '/';

 		$html = '<h2>Webpages - View Webpage Details</h2>

		<div class="fr">
			<a href="?p=pages" class="btn"><span>Back to Overview</span></a>
		</div>

		 <table>
		  <tr>
		   <td><strong>Webpage Title</strong> </td>
		   <td>' . $page['title'] . '</td>
		  </tr>
		  <tr>
		   <td><strong>Domain Name</strong></td>
		   <td>' . $page['domain_name'] . '</td>
		  </tr>
		  <tr>
		   <td><strong>Site</strong></td>
		   <td>' . $page['site_name'] . '</td>
		  </tr>
		  <tr>
		   <td><strong>Page URL</strong></td>
		   <td>' . $page['page_url'] . '</td>
		  </tr>
		  <tr>
		   <td><strong>Last Checked On</strong></td>
		   <td>' . $app->datefmt($page['last_checked']) . '</td>
		  </tr>
		  <tr>
		   <td><strong>Response Code</strong></td>
		   <td>' . $page['latest_response_code'] . '</td>
		  </tr>
		  <tr>
		   <td><strong>Content</strong></td>
		   <td>
				' . ($this->get_page_content($page['domain_name'], $page['current_hash']) != false ? '<iframe id="content_preview" width="1280" height="500" src="' . $server_address . 'temp/' . session_id() . '_viewcontent.html"></iframe>' : '') . '

		   </td>
		  </tr>
		  <tr>
		   <td valign="top"><strong>Last Changed On</strong></td>
		   <td>
				' . $app->datefmt($page['last_changed']) . '
				<br /><br />
				<a href="?p=pages&action=reloadcontent&id=' . $page['id'] . '" class="btn"><span>Re-Check Content</span></a>
				<br /><br />
		   </td>
		  </tr>
		  <tr>
		   <td><strong>Current Content Hash</strong></td>
		   <td>' . $page['current_hash'] . '</td>
		  </tr>
		 </table>
		</form>

 		';

		$app->append_content($html);

	}

  function edit_webpage() {

    $app = $this->app;

    $page_id = $_GET['id'];
    $sql = "SELECT w.*, d.`domain_name`, s.`name` as 'site_name' from `webpages` w LEFT JOIN (`sites` s ) ON s.`id` = w.`site_id` LEFT JOIN (`domains` d) ON d.`id` = w.`domain_id` where w.`id`=:id";
    $page = $app->dao->query($sql,'SelectOne', array(":id"=>$page_id));
		$domain = $app->dao->query("select d.* from `domains` d where d.`id`=:id", "SelectOne", array(":id"=>$page['domain_id']));


 		if (isset($_GET['submit']) && $_GET['submit'] == 't') {

 			$site_id = $page['site_id'];

 			$domains = $app->dao->query("select d.* from `domains` d where d.`site_id`=:site_id", "SelectAll", array(":site_id"=>$site_id));

 			$page_url = $_POST['page_url'];

 			foreach ($domains as $d) {
 				if (strpos($page_url, $d['domain_name']) !== false) {
 					$domain_id = $d['id'];
 					$domain_name = $d['domain_name'];
 				}
 			}

 			//Check if this URL is actually a part of this website
 			if (!isset($domain_id)) {
 				$error = 'This web url (' . substr($page_url, 0, 25) . '..) can not be matched against any of the sites domains..';
 			} else {

 				$result = $app->http->send_request($page_url, array());
 				if ($app->http->test_response($result['headers']) === true) {

 					$title = $_POST['title'];
 					if ($title == '') {
 						$title = $this->find_page_url_title($result['body']);
 					}

 					$page_url = str_replace($domain_name, "", $page_url);

 					$end_protocol = strpos($page_url, "://");

 					if ($end_protocol !== false) { //only trim protocol if it's actually in the URL
 						$end_protocol += 3;
 						$page_url = substr($page_url, $end_protocol, strlen($page_url)-$end_protocol);
 					}

          $version_detection_array = array(
            "em"=>$_POST['version_detection_element'],
            "attr"=>$_POST['version_detection_attribute'],
            "val"=>$_POST['version_detection_value']
          );

          $version_detection = json_encode($version_detection_array);

 					$page_data = array(
            ":id"=>$page_id,
 						":title"=>$title,
 						":url"=>$page_url,
            ":use_https"=>(isset($_POST['use_https']) ? $_POST['use_https'] : 0),
            ":keep_history"=>(isset($_POST['keep_history']) ? $_POST['keep_history'] : 0),
            ":version_detection"=>$version_detection,
            ":version_count"=>$_POST['version_count'],
            ":monitor_response"=>$_POST['monitor_response'],
            ":response_threshold"=>$_POST['response_threshold']
 					);

 					$app->dao->query("UPDATE `webpages` set `title`=:title,`page_url`=:url, `use_https`=:use_https, `keep_history`=:keep_history, `version_detection`=:version_detection, `version_count`=:version_count, `monitor_response`=:monitor_response, `response_threshold`=:response_threshold where `id`=:id ", "Insert", $page_data);

			 		$html = '<h2>Webpages - Edit Webpage</h2>

			 		<br /><br />
					<div align="center">

			 			<h3>We update your web page successfully...YAY!</h3>
			 			<p><strong><em>What would you like to do next?</em></strong></p>

						<a href="?p=pages" class="btn"><span>Webpages Overview</span></a>
						<a href="?p=pages&action=addwebpage" class="btn"><span>Add a new Webpage</span></a>
					</div>';

					$app->append_content($html);

 					return;

 				} else {
 					$error = 'Failed to connect and/or retrieve content from server..';
 				}

 			}
 		}


    $server_address = "http://" . $_SERVER['SERVER_NAME'] . str_replace ($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME'])) . '/';

    $version_detection = json_decode($page['version_detection'], true);
    if ($version_detection === false)
    {
      $version_detection = array(
        "em"=>"",
        "attr"=>"",
        "val"=>""
      );
    }

    $html = '<h2>Webpages - Edit Webpage Details</h2>

    <div class="fr">
      <a href="?p=pages" class="btn"><span>Back to Overview</span></a>
    </div>

    <form action="?p=pages&action=editpage&submit=t&id=' . $page_id . '" method="post">

     ' . ($error != '' ? '<p style="color:red;">' . $error . '</p>' : '') . '

     <table>
      <tr>
       <td><strong>Webpage Title</strong> </td>
       <td><input type="text" name="title" value="' . $page['title'] . '" /></td>
      </tr>
      <tr>
       <td><strong>Page URL</strong></td>
       <td><input type="text" name="page_url" value="' . $domain['domain_name'] . '' . $page['page_url'] . '" /></td>
      </tr>
      <tr>
       <td><strong>Use HTTPS</strong></td>
       <td><input type="checkbox" name="use_https" value="1" ' . ($page['use_https'] == 1 ? 'checked' : '') . ' /></td>
      </tr>
      <tr>
       <td><strong>Keep Version History</strong></td>
       <td><input type="checkbox" name="keep_history" value="1" ' . ($page['keep_history'] == 1 ? 'checked' : '') . ' /></td>
      </tr>
      <tr>
       <td><strong>Version Detection</strong></td>
       <td>
        Element Type: <input type="text" size="5" name="version_detection_element" value="' . $version_detection['em'] . '" /> &nbsp;
        Element Attribute: <input type="text" size="5" name="version_detection_attribute" value="' . $version_detection['attr'] . '" /> &nbsp;
        Attribute Value: <input type="text" size="5" name="version_detection_value" value="' . $version_detection['val'] . '" /> &nbsp;
       </td>
      </tr>
      <tr>
       <td><strong>Number of versions to keep?</strong></td>
       <td><input type="text" name="version_count" value="' . $page['version_count'] . '" /></td>
      </tr>
      <tr>
       <td><strong>Monitor Response Times</strong></td>
       <td><input type="checkbox" name="monitor_response" value="1" ' . ($page['monitor_response'] == 1 ? 'checked' : '') . ' /></td>
      </tr>
      <tr>
       <td><strong>Response Threshold (seconds)</strong></td>
       <td><input type="text" name="response_threshold" value="' . $page['response_threshold'] . '" /></td>
      </tr>
		  <tr>
		   <td></td>
		   <td><input type="submit" value="Update Web Page" /></td>
		  </tr>

     </table>

    </form>

    ';

    $app->append_content($html);

  }

	function reload_page_content($reload=true, $page_id=null) {

		$app = $this->app;


		if (!isset($page_id))
    {
      $page_id = $_GET['id'];
      $comment = "Manually triggered reload..";
    }
    else
    {
      $comment = "Background content reload..";
    }

		$sql = "SELECT w.*, d.`domain_name`, s.`name` as 'site_name' from `webpages` w LEFT JOIN (`sites` s ) ON s.`id` = w.`site_id` LEFT JOIN (`domains` d) ON d.`id` = w.`domain_id` where w.`id`=:id";
		$page = $app->dao->query($sql,'SelectOne', array(":id"=>$page_id));



		if ($page['use_https'] == 1) {
			$page_url = 'https://';
		} else {
			$page_url = 'http://';
		}

		$page_url .= $page['domain_name'] . $page['page_url'];

    $start_time = microtime(true);
		$result = $app->http->send_request($page_url, array());

    $response_time = microtime(true) - $start_time;

		$page['current_hash'];

    if ($page['monitor_response'] == 1)
    {

  	 	list($resp_proto, $resp_code, $resp_text) = explode(" ", $result['headers'][0]);

      $response_data = array(
        ":page_id"=>$page['id'],
        ":response_time"=>$response_time,
        ":response_code"=>$resp_code,
        ":comment"=>$comment
      );

      $response_result = $app->dao->query("INSERT into `response` (`page_id`,`timechecked`,`response_time`,`response_code`,`comment`) VALUES (:page_id, NOW(), :response_time, :response_code, :comment)", "Insert", $response_data);

    }

		if ($app->http->test_response($result['headers']) === true) {

			$content = $result['body'];

			$dom = new DOMDocument;
			libxml_use_internal_errors(true);
			$dom->loadHTML($content);
			libxml_use_internal_errors(false);

			$xpath = new DOMXPath($dom);
			$xbody = $xpath->query('/html/body');

			$body = $dom->saveXml($xbody->item[0]);

			$hash = md5($body);

			$foldername = "extract/" . str_replace(".", "_", $page['domain_name']);
			if (!file_existS($foldername)) { mkdir($foldername); }

			$foldername .= "/" . $hash . "/";
			if (!file_existS($foldername)) { mkdir($foldername); }

			if (file_exists($foldername.'content.html.gz')) {
				unlink($foldername.'content.html.gz');
			}

			$fh = fopen($foldername.'content.html.gz', 'w');
			fwrite($fh, gzcompress($result['body']));
			fclose($fh);

			list($proto, $code_num, $code_str) = explode(" ", $result['headers'][0]);

			$response_code = $code_num . ' ' . $code_str;

			if ($hash != $page['current_hash']) {

			  $data = array(
			   ":hash"=>$hash,
			   ":id"=>$page['id'],
			   ":rcode"=>$response_code
			  );

			  $app->dao->query("UPDATE `webpages` set `last_changed`=now(), `last_checked`=now(), `current_hash`=:hash, `latest_response_code`=:rcode where `id`=:id","Update",$data);

			  $data = array(
			   ":hash"=>$hash,
			   ":id"=>$page['id']
			  );

			  $app->dao->query("INSERT INTO `change_log` (`webpage_id`, `content_hash`, `time_detected`) VALUES (:id, :hash, now())","Insert",$data);

			} else {
			  $data = array(
			   ":id"=>$page['id'],
			   ":rcode"=>$response_code
			  );
			  $app->dao->query("UPDATE `webpages` set `last_checked`=now(), `latest_response_code`=:rcode where `id`=:id", "Update", $data);
			}

		} else {

		 if (isset($result['headers']) && is_array($result['headers'])) {
			  list($proto, $code_num, $code_str) = explode(" ", $result['headers'][0]);

		   	  $response_code = $code_num . ' ' . $code_str;

			  $data = array(
			   ":id"=>$page['id'],
			   ":rcode"=>$response_code
			  );

			  $app->dao->query("UPDATE `webpages` set `last_checked`=now(), `latest_response_code`=:rcode where `id`=:id", "Update", $data);
		 }
		}

		if ($reload == true) {
			header('location: ' . $_SERVER['HTTP_REFERER']);
			die();
		}

	}

	function get_page_content($domain, $hash) {

		$content = '';
		$foldername = "extract/" . str_replace(".", "_", $domain) . "/" . $hash . "/";

		if (file_exists($foldername.'content.html.gz')) {
			$fh = fopen($foldername.'content.html.gz', 'r');
			$content = gzuncompress(fread($fh, filesize($foldername.'content.html.gz')));
			fclose($fh);

			$dom = new DOMDocument;

			libxml_use_internal_errors(true);
			$dom->loadHTML($content);
			libxml_use_internal_errors(false);

			$anchors = $dom->getElementsByTagName('a');
			foreach ($anchors as $a) {
				$a->setAttribute("href","#");
				$a->setAttribute("onclick","javascript:void();");
			}

			//Make page relative to the original URL to fix CSS and image breakage issues
			$head = $dom->getElementsByTagName('head')->item(0);
			$base = $dom->createElement('base');
			$base->setAttribute('href', 'http://' . $domain . '/');
			$head->parentNode->insertBefore($base, $head);

			$content = $dom->saveHTML();
			$fh = fopen('temp/' . session_id() . '_viewcontent.html', 'w');
			fwrite($fh, $content);
			fclose($fh);

			return true;

		}

		return false;
	}

 	function delete_webpage() {

 		$app = $this->app;

 	    if (isset($_GET['id'])) {

         	$page_data = array(
 				":id"=>$_GET['id'],
 			);

 			$app->dao->query("DELETE FROM `webpages` where `id`=:id ", "Delete", $page_data);

	 		$html = '<h2>Webpages - Delete Webpage</h2>

	 		<br /><br />
			<div align="center">

	 			<h3>We deleted the web page successfully!</h3>
	 			<p><strong><em>What would you like to do next?</em></strong></p>

				<a href="?p=pages" class="btn"><span>Webpages Overview</span></a>
        <a href="?p=pages&action=addwebpage" class="btn"><span>Add a new Webpage</span></a>
			</div>';

			$app->append_content($html);

 			return;

 	    }

 		$html = '<h2>Webpages - Delete Webpage</h2>

 		<br /><br />
		<div align="center">

 			<h3>Whoops! We couldn\'t find that page to delete it..</h3>
 		    <p><strong><em>What would you like to do next?</em></strong></p>

			<a href="?p=pages" class="btn"><span>Return to Webpages Overview</span></a>
      <a href="?p=pages&action=addwebpage" class="btn"><span>Add a new Webpage</span></a>
		</div>';

		$app->append_content($html);

 	}

 	function find_page_url_title($html) {

 		$title = '';

 		$dom = new DOMDocument;

		libxml_use_internal_errors(true);
		$dom->loadHTML($html);
		$titles = $dom->getElementsByTagName('title');
		libxml_use_internal_errors(false);

		foreach ($titles as $t) {
			$title .= $t->textContent;
		}

 		return $title;

 	}

 }

?>
