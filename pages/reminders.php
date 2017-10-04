<?php

class page_reminders {

	function page_reminders($app) {

		$this->app = $app;

		$public_functions = array("processreminder");
		$action = (isset($_GET['action']) ? $_GET['action'] : "" );

		if (in_array($action, $public_functions)) {
			$this->public_access = true;

		}

	}

	function script() {


	}

	function content() {


		$app = $this->app;

		$action = (isset($_GET['action']) ? $_GET['action'] : "" );

		switch ($action) {

			case 'outstanding_reminders' :
				$html = $this->outstanding_reminders();
			break;

			case 'processreminder' :
				$html = $this->process_reminder();
			break;
			case 'viewreminder' :
				$html = $this->view_reminder();
			break;
			case 'bulk_action' :
				$this->perform_bulk_action();
			default :
				$html = $this->get_reminders_overview();
			break;
		}

		$app->append_content($html);

	}

	function get_reminders_overview() {


		$app = $this->app;

		if (isset($_POST['filter_status'])) {
			$_SESSION['reminders_filter_status'] = $_POST['filter_status'];
		}

		if (isset($_POST['filter_time'])) {
			$_SESSION['reminders_filter_time'] = $_POST['filter_time'];
		}

		if (isset($_POST['filter_subscriber'])) {
			$_SESSION['reminders_filter_subscriber'] = $_POST['filter_subscriber'];
		}

		if (isset($_SESSION['reminders_filter_status'])) {
			$filter_status = $_SESSION['reminders_filter_status'];
		}

		if (isset($_SESSION['reminders_filter_time'])) {
			$filter_time = $_SESSION['reminders_filter_time'];
		} else {
			$filter_time = '7';
		}

		if (isset($_SESSION['reminders_filter_subscriber'])) {
			$filter_subscriber = $_SESSION['reminders_filter_subscriber'];
		}

		$sql = "select reminders.* from reminders ";
		$qry_data = array();
		$where = false;

		if (isset($filter_status) && $filter_status != "") {

		  $sql .= "WHERE reminders.`status` = :status ";
		  $qry_data[":status"] = $filter_status;
		  $where = true;

		}

		if (isset($filter_time) && $filter_time != "") {

		  $sql_filter_time = $filter_time*-1;
		  $sql .= ($where == true ? 'AND ' : 'WHERE ') . " reminders.`reminder_time` > DATE_ADD(NOW(), INTERVAL $sql_filter_time DAY )";
		  $where = true;
		}

		if (isset($filter_subscriber) && $filter_subscriber != "") {

		  $sql .= ($where == true ? 'AND ' : 'WHERE ') . " reminders.`subscriber_id`= :SUBID";

		  $qry_data[":SUBID"] = $filter_subscriber;
		  $where = true;

		}

		//echo '<p><br /><br /></p><p>' . $sql . '</p>';

		$sql .= " ORDER BY `reminder_time` DESC";

		$reminders = $app->dao->query($sql, "SelectAll", $qry_data);

		$reminders_html = '';

		foreach ($reminders as $r) {

			 $subscriber = $this->get_subscriber($r['subscriber_id']);
			 $webpage = $this->get_webpage($r['webpage_id']);

			 $reminders_html .= '<tr>
			 				  <td><input type="checkbox" name="selected[]" value="' . $r['id'] . '" /></td>
			 					<td>' . $subscriber['name'] . '</td>
			 					<td>' . $webpage['title'] . '</td>
			 					<td>' . ucfirst($r['status']) . '</td>
								<td align="center">' . $this->decision_shorthand($r['subscriber_decision']) . '</td>
		 						<td>' . date("h:i d-M-Y", strtotime($r['reminder_time'])) . '</td>
			 					<td>' . (substr($r['status_viewed_date'],0,4) == '0000' ? 'never' : date("h:i d-M-Y", strtotime($r['status_viewed_date'])) ) . '</td>
			 					<td>' . (substr($r['status_checked_date'],0,4) == '0000' ? 'never' : date("h:i d-M-Y", strtotime( $r['status_checked_date'])) ) . '</td>
			 					<td>' . (substr($r['status_completed_date'],0,4) == '0000' ? 'never' : date("h:i d-M-Y", strtotime($r['status_completed_date'])) ) . '</td>
			 					<td>
			 						<a href="?p=reminders&action=viewreminder&id=' . $r['id'] . '" class="btn"><span>View Details</span></a>
			 					</td>
			 				  </tr>';
		}

		$subscribers = $app->dao->query("SELECT `subscriber`.* from `subscriber` order by `name` ASC", "SelectAll");
		$subscriber_opts = '<option value="">Any Subscriber</option>';

		foreach ($subscribers as $sub) {
			if (isset($filter_subscriber) && $filter_subscriber == $sub['id']) { $subscriber_opts .= '<option selected value="' . $sub['id'] . '">' . $sub['name'] . '</option>'; }
			else { $subscriber_opts .= '<option value="' . $sub['id'] . '">' . $sub['name'] . '</option>'; }
		}

		$html = '

		 <h2>Reminders</h2>
		 <div class="fl">
		 <form action="?p=reminders" method="post">
		  <select name="filter_status">
		   <option value="">Any Status</option>
		   <option ' . (isset($filter_status) && $filter_status == 'new' ? 'selected' : '') . ' value="new">New</option>
		   <option ' . (isset($filter_status) && $filter_status == 'viewed' ? 'selected' : '') . ' value="viewed">Viewed</option>
		   <option ' . (isset($filter_status) && $filter_status == 'checked' ? 'selected' : '') . ' value="checked">Checked</option>
		   <option ' . (isset($filter_status) && $filter_status == 'completed' ? 'selected' : '') . ' value="completed">Completed</option>
		  </select>
		  <select name="filter_time">
		   <option ' . (isset($filter_time) && $filter_time == '1' ? 'selected' : '') . ' value="1">In the last Day</option>
		   <option ' . (isset($filter_time) && $filter_time == '7' ? 'selected' : '') . ' value="7">In the last 7 Days</option>
		   <option ' . (isset($filter_time) && $filter_time == '30' ? 'selected' : '') . ' value="30">In the last 30 Days</option>
		   <option ' . (isset($filter_time) && $filter_time == '60' ? 'selected' : '') . ' value="60">In the last 60 days</option>
		   <option ' . (isset($filter_time) && $filter_time == '90' ? 'selected' : '') . ' value="90">In the last 90 days</option>
		   <option value="">Any Time</option>
		  </select>
		  <select name="filter_subscriber">
		   ' . $subscriber_opts . '
		  </select>
		  <input type="submit" value="Apply" />
		 </form>
		</div>
		 <div style="float: right;">
		 	<a class="btn" href="?p=reminders"><span>Reload Page</span></a>
			<a class="btn" href="?p=reminders&action=outstanding_reminders"><span>Oustanding / Overdue Reminders </span> </a>
		 </div>
		 <h3>Reminders Overview</h3>
		 <form action="?p=reminders&action=bulk_action" method="post">
		 <table width="100%">
		 	<thead>
		 		<tr>
					<td>Select</td>
		 			<td>Subscriber Name</td>
		 			<td>Webpage Title</td>
		 			<td>Status</td>
					<td align="center">Updates Required</td>
		 			<td>Initiated At</td>
		 			<td>Viewed At</td>
		 			<td>Checked At</td>
		 			<td>Completed At</td>
		 			<td></td>
		 		</tr>
		 	</thead>
		 	<tbody>
		 	 ' . $reminders_html . '
		 	</tbody>
			<tfoot>
			  <tr>
				 <td colspan="10" style="background: #e1e1e1; padding: 8px;">
				  <strong>With selected: </strong>
					<select name="bulk_action">
						<option>Select an action...</option>
						<option value="status_completed">Mark as completed</option>
					</select>
					<input type="submit" value="Go" />
				 </td>
				</tr>
			<tfoot>
		 </table>
		 </form>

		';

		return $html;

	}

	function decision_shorthand($decision)
	{

		switch ($decision)
		{
			case 'needs to be updated as follows' : return 'Yes - Update'; break;
			case 'is no longer required on our website' : return 'Yes - Unpublish'; break;
			default:
			 return 'No';
			break;
		}
		return 'No';
	}

	function process_reminder() {

		$app = $this->app;

		$server_address = "http://" . $_SERVER['SERVER_NAME'] . str_replace ($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME'])) . '/';

		$reminder_sql = "select sup.* from `reminders` sup WHERE `id` = :ID";
		$reminder = $app->dao->query($reminder_sql, "SelectOne", array(":ID"=>$_GET['id']));

		$subscription_sql = "select sup.* from `subscriptions` sup WHERE `id` = :ID";
		$subscription = $app->dao->query($subscription_sql, "SelectOne", array(":ID"=>$reminder['subscription_id']));

		$page = $this->get_webpage($subscription['webpage_id']);
		$subscriber = $this->get_subscriber($subscription['subscriber_id']);
		$subscriber_names = explode(" ", $subscriber['name']);

		if (isset($_GET['submit'])) {

			if ($reminder['status'] == 'viewed') {

				$data = array(
				 ":id"=>$reminder['id'],
				 ":decision"=>$_POST['decision'],
				 ":comment"=>$_POST['content_updates']
				);

				$app->dao->query("update `reminders` set `status`='checked', `status_checked_date`=NOW(), `subscriber_comment`=:comment, `subscriber_decision`=:decision where `id`=:id", "Update", $data);

				$data = array(
				 ":id"=>$reminder['id'],
				);

				$data = array(
				 ":id"=>$reminder['subscription_id'],
				);

				$app->dao->query("update `reminders` set `status`='checked', `status_checked_date`=NOW(), `subscriber_comment`='Checked by implication of a later reminder from the same subscription' where `subscription_id`=:id and status NOT IN ('checked','completed')", "Update", $data);

				$admin_email = "Hey {admin_fname},\r\n\r\n";
				$admin_email .= $subscriber_names[0] . " has just reviewed their website check reminder for '" . $page['title'] . "'. Their response was as follows - \r\n\r\n";
				$admin_email .= " ** This web page content " . $_POST['decision'] . " ** \r\n\r\n";
				$admin_email .= "" . ($_POST['content_updates'] == '' ? '(No content updates supplied)' : $_POST['content_updates']) . "\r\n\r\n";
				$admin_email .= "You can view this content review on the website checker, and add comments for your own record if you wish. http://intapps.mpdc.govt.nz/websitechecker/ \r\n\r\n";
				$admin_email .= "If you don't wish to received these notifications any longer, please notify your developer to have them turned off. \r\n\r\n";
				$admin_email .= "Bye for now,\r\n\r\nWebsite Checker";

				$m = new mailer();
				$admins = $app->dao->query("Select * from `user` where `notifications`=1");

				foreach ($admins as $admin) {

					$admin_names = explode(" ", $admin['realname']);
					$to = array("name"=>$admin['realname'], "address"=>$admin['email_address']);
					$from = array("name"=>"websitechecker@mpdc.govt.nz", "address"=>"webmaster@mpdc.govt.nz");
					$subject = "Website Checker - New Web Page Review Received";
					$body = str_replace("{admin_fname}",$admin_names[0],$admin_email);
					$m->create_message($to, $from, $subject, $body, 'text/plain');
					$m->send_message();

				}

			}


			$html = '<h2>Webpages - Review Website Page Content</h2>
			<br />
			<p>Thanks ' . $subscriber_names[0] . '! Your website content review for "' . $page['title'] . '" has been saved and sent to the website administrators to be actioned as required.</p>';


			$pending_status = array("new", "viewed");

			$subscriber_reminders = $app->dao->query("select sup.* from `reminders` sup WHERE `subscriber_id` = :ID", "SelectAll", array(":ID"=>$subscriber['id']));
			$pending_reminders = array();
			foreach ($subscriber_reminders as $rem) {
				if (in_array($rem['status'], $pending_status)) {
					$pending_reminders[] = $rem;
				}
			}

			if (count($pending_reminders) != 0) {
				$html .= '<br /><hr /><br />
						<h4>Other incomplete reviews</h4>
						';

				foreach ($pending_reminders as $rem) {
					$wpage = $this->get_webpage($rem['webpage_id']);
					$html .= '<p>' . $wpage['title'] . ' - ' . date("d M Y", strtotime($rem['reminder_time'])) . ' &nbsp;&nbsp;&nbsp; <a href="?p=reminders&action=processreminder&id=' . $rem['id'] . '" class="btn"><span>View Details</span></a> </p>';

				}

			}


			return $html;

		}

		switch ($reminder['status']) {

			case 'checked' :
			case 'completed' :


				$html = '<h2>Webpages - Review Website Page Content</h2>
				<br />
				<p>Thanks! You already checked this website before - if you need to make an update to your page before the next reminder is sent, please lodge a CRM for website updates in <a href="http://authsvr/iservice/">Authority</a>.</p>';

				return $html;

			break;
			default :

			break;

		}



		$html = '

		<h2>Webpages - Review Website Page Content</h2>
		<form action="?p=reminders&action=processreminder&id=' . $reminder['id'] . '&submit=t" method="post">
 		<table>
		  <tr>
		   <td><strong>Webpage Title</strong> </td>
		   <td>' . $page['title'] . '</td>
		  </tr>
		  <tr>
		   <td><strong>Page Address (URL)</strong></td>
		   <td><a target="_blank" href="http://' . $page['domain_name'] . '' . $page['page_url'] . '">' . $page['domain_name'] . '' . $page['page_url'] . '</a> (click to open live page)</td>
		  </tr>
		  <tr>
		   <td valign="top"><strong>Page Content</strong></td>
		   <td>
				' . ($this->get_page_content($page['domain_name'], $page['current_hash']) != false ? '<iframe id="content_preview" width="1280" height="550" src="' . $server_address . 'temp/' . session_id() . '_viewcontent.html?q=' . md5(time()) . '"></iframe>' : '') . '

		   </td>
		  </tr>
		  <tr>
		   <td valign="top"><strong>Review Decisions</strong></td>
		   <td>
				<span>Reviewed by ' . $subscriber['name'] . ' on ' . date("d M Y") . '</span>
				<br />
				<p>This web page content <select name="decision">
					<option value="needs to be updated as follows"> needs to be updated as follows </option>
					<option value="is no longer required on our website"> is no longer required on our website </option>
					<option value="is still current and does not require any changes"> is still current and does not require any changes </option>
					</select>&nbsp;.
				</p>

		   </td>
		  </tr>
		  <tr>
		   <td><b>Content updates - </b></td>
		   <td>
				<textarea style="width: 1280px; height: 100px;" name="content_updates"></textarea>
			</td>
		   </tr>
		   <tr>
		    <td></td>
		    <td>
		 	<input type="submit" value="Submit Content Review" />
		 	</td>
		   </tr>
		 </table>
		</form>

		';

		if ($reminder['status'] == 'new') {
			$reminder_sql = "update `reminders` set `status`='viewed', `status_viewed_date`=NOW() WHERE `id` = :ID";
			$app->dao->query($reminder_sql, "Update", array(":ID"=>$_GET['id']));
		}

		return $html;

	}

	function view_reminder() {
		$app = $this->app;

		$server_address = "http://" . $_SERVER['SERVER_NAME'] . str_replace ($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME'])) . '/';

		$reminder_sql = "select sup.* from `reminders` sup WHERE `id` = :ID";
		$reminder = $app->dao->query($reminder_sql, "SelectOne", array(":ID"=>$_GET['id']));

		$subscription_sql = "select sup.* from `subscriptions` sup WHERE `id` = :ID";
		$subscription = $app->dao->query($subscription_sql, "SelectOne", array(":ID"=>$reminder['subscription_id']));

		$page = $this->get_webpage($subscription['webpage_id']);
		$subscriber = $this->get_subscriber($subscription['subscriber_id']);
		$subscriber_names = explode(" ", $subscriber['name']);

		if (isset($_GET['submit'])) {

			$data = array(
			 ":id"=>$reminder['id'],
			 ":status"=>$_POST['status'],
			 ":comment"=>$_POST['admin_comment']
			);

			$app->dao->query("update `reminders` set `status`=:status, `admin_comment`=:comment where `id`=:id", "Update", $data);


			if ($_POST['status'] == 'completed') {

				$data = array(
				 ":id"=>$reminder['id']
				);

				$app->dao->query("update `reminders` set `status_completed_date`=NOW() where `id`=:id", "Update", $data);
			}

			$sub_email = "Hey " . $subscriber_names[0] . ",\r\n\r\n";
			$sub_email .= "An admin left a comment on your website content review for  '" . $page['title'] . "'. Their comment was as follows - \r\n\r\n";
			$sub_email .= "" . $_POST['admin_comment'] . "\r\n\r\n";
			$sub_email .= "Your decision was '.." . $reminder['subscriber_decision'] . "'\r\n\r\n";
			$sub_email .= "And changes to content supplied as follows - \r\n\r\n" . $reminder['subscriber_comment'] . "\r\n\r\n";
			$sub_email .= "Bye for now,\r\n\r\nWebsite Checker";

			if (isset($_POST['notify_subscriber'])) {

				$m = new mailer();
				$to = array("name"=>$subscriber['name'], "address"=>$subscriber['email']);
				$from = array("name"=>"websitechecker@mpdc.govt.nz", "address"=>"webmaster@mpdc.govt.nz");
				$subject = "Website Checker - A Comment On Your Webpage Review";
				$body = $sub_email;
				$m->create_message($to, $from, $subject, $body, 'text/plain');
				$m->send_message();

			}



			$html = '<h2>Reminders - View Reminder Details</h2>
			<br />
			<p>Thanks! Your comments for "' . $page['title'] . '" have been saved.</p>';
			$html .= '<br /><div align="center">
				<a href="index.php?p=reminders&action=viewreminder&id=' . $reminder['id'] . '" class="btn"><span>View Reminder</span></a> &nbsp;
				<a href="index.php?p=reminders" class="btn"><span>Return to Reminders Overview</span></a>
			</div>';

			return $html;

		}

		$status_message = '';

		switch ($reminder['status']) {

			case 'checked' :
			case 'completed' :

					$status_message = 'Reviewed by ' . $subscriber['name'] . ' on ' . date("d M Y", strtotime($reminder['status_checked_date']));
			break;
			default :
					$status_message = 'Reminder sent to  ' . $subscriber['name'] . ' on ' . date("d M Y", strtotime($reminder['reminder_time']));
			break;

		}

		$statuses = array("new","viewed","checked","completed");

		$status_opts = '';
		foreach ($statuses as $status) {
			if ($status == $reminder['status']) {
				$status_opts .= '<option value="' . $status . '" selected>' . ucfirst($status) . '</option>';
			} else {
				$status_opts .= '<option value="' . $status . '">' . ucfirst($status) . '</option>';
			}
		}


		$html = '

		<h2>Reminders - View Reminder Details</h2>
		<form action="?p=reminders&action=viewreminder&id=' . $reminder['id'] . '&submit=t" method="post">
 		<table>
		  <tr>
		   <td><strong>Webpage Title</strong> </td>
		   <td>' . $page['title'] . '</td>
		  </tr>
		  <tr>
		   <td><strong>Page Address (URL)</strong></td>
		   <td><a target="_blank" href="http://' . $page['domain_name'] . '' . $page['page_url'] . '">' . $page['domain_name'] . '' . $page['page_url'] . '</a> (click to open live page)</td>
		  </tr>
		  <tr>
		   <td valign="top"><strong>Page Content</strong></td>
		   <td>
				' . ($this->get_page_content($page['domain_name'], $page['current_hash']) != false ? '<iframe id="content_preview" width="1280" height="500" src="' . $server_address . 'temp/' . session_id() . '_viewcontent.html"></iframe>' : '') . '

		   </td>
		  </tr>
		  <tr>
		   <td valign="top"><strong>Review Decisions</strong></td>
		   <td>
				<span>' . $status_message . '</span>
				<br />
				<p>This web page content' . ($reminder['subscriber_decision'] == '' ? ' is yet to be reviewed ' : ' ' . $reminder['subscriber_decision']) . '</p>

		   </td>
		  </tr>
		  <tr>
		   <td><b>Content updates - </b></td>
		   <td>
				' . ($reminder['subscriber_comment'] == '' ? '' : nl2br($reminder['subscriber_comment'])) . '
			</td>
		   </tr>
		   <tr>
		   <td><b>Change Status - </b></td>
			<td>
		 	 <select name="status">' . $status_opts . '</select>
			</td>
		   </tr>
		   <tr>
		   <td><b>Notes / Comments - </b></td>
			<td>
		 	 <textarea style="width: 1280px; height: 100px;" name="admin_comment">' . $reminder['admin_comment'] . '</textarea>
			</td>
		   </tr>
		   <tr>
		    <td></td>
		    <td>
		 	<input type="submit" value="Save Comment" />  <div style="float: right;"><input type="checkbox" name="notify_subscriber" checked /> - <b>Notify Subscriber By Email</b> </div>
		 	</td>
		   </tr>
		 </table>
		</form>

		';

		if ($reminder['status'] == 'new') {
			$reminder_sql = "update `reminders` set `status`='viewed', `status_viewed_date`=NOW() WHERE `id` = :ID";
			$app->dao->query($reminder_sql, "Update", array(":ID"=>$_GET['id']));
		}

		return $html;

	}

	function get_page_content($domain, $hash) {

		$app = $this->app;
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

	function get_webpage($id) {


		$app = $this->app;

		$sql = "SELECT w.*, d.`domain_name`, s.`name` as 'site_name' from `webpages` w LEFT JOIN (`sites` s ) ON s.`id` = w.`site_id` LEFT JOIN (`domains` d) ON d.`id` = w.`domain_id` where w.`id`=:ID";

		$webpage = $app->dao->query($sql, "SelectOne", array(":ID"=>$id) );

		return $webpage;

	}

	function get_subscriber($id) {

		$app = $this->app;

		$sql = "select sub.* from `subscriber` sub WHERE `id`=:ID";

		$subscriber = $app->dao->query($sql, "SelectOne", array(":ID"=>$id) );

		return $subscriber;

	}

	function outstanding_reminders() {

		$app = $this->app;
		$html = '';

		//Sets how many days are considered overdue
		$days_odue = 30;
		if (isset($_SESSION['reminders_days_odue']) && $_SESSION['reminders_days_odue'] != "")
		{
			$days_odue = $_SESSION['reminders_days_odue'];
		}

		if (isset($_POST['overdue_time'])) {
			$days_odue = $_POST['overdue_time'];
			$_SESSION['reminders_days_odue'] = $days_odue;
		}

		$sql = "select sub.* from `subscriber` sub ORDER BY sub.`name`";

		$subscribers = $app->dao->query($sql, "SelectAll");

		$html = '<h2>Outstanding / Overdue Reminders</h2>

				<form action="?p=reminders&action=outstanding_reminders" method="post">
			 	Overdue time (days):
			   	<select name="overdue_time">
			   		<option ' . ($days_odue == 14 ? 'selected ' : '') . 'value="14">14 Days</option>
			   		<option ' . ($days_odue == 30 ? 'selected ' : '') . 'value="30">30 Days</option>
			   		<option ' . ($days_odue == 60 ? 'selected ' : '') . 'value="60">60 Days</option>
			   		<option ' . ($days_odue == 90 ? 'selected ' : '') . 'value="90">90 Days</option>
			   		<option ' . ($days_odue == 10000 ? 'selected ' : '') . 'value="10000">Never</option>
			   	</select>
					<input type="submit" value="Apply">
				</form>

				<div class="fr">
					<a href="?p=reminders" class="btn"><span>Reminders Overview</span></a>
				</div>

				<table width="100%">
					';

		foreach ($subscribers as $s) {


			$subscription_rows = '';

			$sql = "select sup.*, w.`title` as 'webpagetitle', s.`name` as 'sitename' from `subscriptions` sup INNER JOIN (`webpages` w) ON w.`id` = sup.`webpage_id` INNER JOIN (`sites` s) ON s.`id` = w.`site_id` where sup.`subscriber_id`=:SUBID ORDER BY w.`title` ";

			$subscriptions = $app->dao->query($sql, "SelectAll", array(":SUBID"=>$s['id']));
			foreach ($subscriptions as $sup) {

					$last_checked = $app->dao->query("SELECT `id`, `status_checked_date` from `reminders` where `subscription_id`=:SUPID ORDER BY `status_checked_date` DESC", "SelectOne", array(":SUPID"=>$sup['id']));
					$last_viewed = $app->dao->query("SELECT `id`, `status_viewed_date` from `reminders` where `subscription_id`=:SUPID ORDER BY `status_viewed_date` DESC", "SelectOne", array(":SUPID"=>$sup['id']));
					$date_diff = (strtotime($last_checked['status_checked_date']) - strtotime($sup['last_reminder_sent']));
					if ( $date_diff < 0 && $date_diff < ($days_odue*24*60*60*-1)) {

						$subscription_rows .= '<tr>
																		<td>' . $sup['id'] . '</td>
																		<td><a target="_blank" href="?p=pages&action=viewpage&id=' . $sup['webpage_id'] . '">' . $sup['webpagetitle'] . '</a> (' . $sup['sitename'] . ')</td>
																		<td>' . (substr($sup['last_reminder_sent'], 0, 4) != 0000 ? date("d M Y", strtotime($sup['last_reminder_sent'])) : 'never') . '</td>
																		<td><span title="Reminder ID ' . $last_viewed['id'] . '">' . (substr($last_viewed['status_viewed_date'], 0, 4) != 0000 ? date("d M Y", strtotime($last_viewed['status_viewed_date'])) : 'never') . '</span></td>
																		<td><span title="Reminder ID ' . $last_checked['id'] . '">' . (substr($last_checked['status_checked_date'], 0, 4) != 0000 ? date("d M Y", strtotime($last_checked['status_checked_date'])) : 'never') . '</span></td>
																	 </tr>';

					}
			}
			if ($subscription_rows != '')
			{
				$html .= '<thead>
					<tr>
						<td width="55%" >' . $s['name'] . '</td>
						<td width="15%" >Email: ' . $s['email'] . '</td>
						<td width="15%" >Active: ' . ($s['is_active'] == 1 ? 'Yes' : 'No') . '</td>
						<td width="15%" ></td>
					</tr>
				</thead>
				<tbody>
					<tr>
					 <td colspan="4">
					 	<table width="100%">
							<thead>
							 <tr>
							  <td width="3%" style="background: #888;">ID</td>
							  <td width="52%" style="background: #888;">Web Page (Site)</td>
								<td width="15%" style="background: #888;">Last Reminder Sent</td>
								<td width="15%" style="background: #888;">Last Checked</td>
								<td width="15%" style="background: #888;">Last Review Received</td>
							 </tr>
							</thead>
							<tbody>
								' . $subscription_rows . '
							</tbody>
						</table>
						<br /><br />
					 </td>
					</tr>
				</tbody>';
			}
		}

		$html .= '
			</table>';

		$app->append_content($html);


	}

	function perform_bulk_action()
	{
		switch ($_POST['bulk_action'])
		{
			case "status_completed" :

				$app = $this->app;
				foreach ($_POST['selected'] as $id)
				{
					$data = array(":id"=>$id, ":status"=>"completed", ":comment"=>"Bulk updated by " . $app->auth->user['realname']);
					$app->dao->query("update `reminders` set `status`=:status, `admin_comment`=:comment where `id`=:id", "Update", $data);
				}

			break;
		}
	}

}

?>
