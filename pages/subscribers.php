<?php


 class page_subscribers {

	function page_subscribers($app) {

 		$this->app = $app;

	}

 	function script() {

 	}

 	function content() {

 		$app = $this->app;

 		if (isset($_GET['action'])) {
 			switch ($_GET['action']) {

 				case 'addsubscriber' : $this->add_subscriber(); break;
 				case 'addsubscription' : $this->add_subscription(); break;
				case 'viewsubscriptions' : $this->view_subscriptions(); break;
 			  case 'editsubscriber' : $this->edit_subscriber(); break;
				case 'deletesubscriber' : $this->delete_subscriber(); break;
				case 'deletesubscription' : $this->delete_subscription(); break;
 				default : $this->get_overview(); break;

 			}
 		} else {
 			$this->get_overview();
 		}

 	}

 	function get_overview() {

 		$app = $this->app;

		$sql = "select sub.* from `subscriber` sub ORDER BY sub.`name`";

		$subscribers = $app->dao->query($sql, "SelectAll");

		$html = '<h2>Subscribers</h2>
		<div class="fr">
			<a href="?p=subscribers&action=addsubscriber" class="btn"><span>Add Subscriber</span></a>
		</div>
				<table width="100%">
					<thead>
						<tr>
							<td>Name</td>
							<td>Email</td>
							<td>Is Active</td>
							<td></td>
						</tr>
					</thead>
					<tbody>';

		foreach ($subscribers as $s) {
			$html .= '
				<tr>
					<td>' . $s['name'] . '</td>
					<td>' . $s['email'] . '</td>
					<td>' . ($s['is_active'] == 1 ? 'Yes' : 'No') . '</td>
					<td>
						<a href="?p=subscribers&action=viewsubscriptions&id=' . $s['id'] . '" class="btn"><span>Subscriptions</span></a>
						<a href="?p=subscribers&action=editsubscriber&id=' . $s['id'] . '" class="btn"><span>Edit</span></a>
						<a href="?p=subscribers&action=deletesubscriber&id=' . $s['id'] . '" class="btn"><span>Delete</span></a>
					</td>
				</tr>
			';
		}

		$html .= '</tbody>
			</table>';

		$app->append_content($html);

 	}

 	function add_subscriber() {

 		$app = $this->app;

 		$error = '';

 		if (isset($_GET['submit']) && $_GET['submit'] == 't') {

 			$email = $_POST['email'];

 			$sub_exists = $app->dao->query("select sub.* from `subscriber` sub where sub.`email`=:email", "SelectOne", array(":email"=>$email));

 			//Check if this URL is actually a part of this website
 			if (isset($sub_exists['id'])) {
 				$error = 'This email is already in use with an existing subscriber - you can add a new web page subscription to the existing subscriber or use a different email address..';
 			} else {

 				$page_data = array(
 					":name"=>$_POST['name'],
 					":email"=>$email
 				);

 				$app->dao->query("INSERT INTO `subscriber` (`name`,`email`,`is_active`) VALUES (:name, :email, 1) ", "Insert", $page_data);
 				$sub_id = $app->dao->insert_id();

		 		$html = '
		 		<h2>Subscribers - Add Subscriber</h2>

		 		<br /><br />
				<div align="center">

		 			<h3>We added your new subscriber successfully...YAY!</h3>
		 			<p><strong><em>What would you like to do next?</em></strong></p>

					<a href="?p=subscribers&action=viewsubscriptions&id=' . $sub_id . '" class="btn"><span>View/Edit Subscriptions</span></a>
					<a href="?p=subscribers&action=addsubscriber" class="btn"><span>Add another Subscriber</span></a>
				</div>';

				$app->append_content($html);

 				return;


 			}
 		}


 		$html = '

 		<h2>Subscribers - Add New Subscriber</h2>

		<div class="fr">
			<a href="?p=pages" class="btn"><span>Back to Overview</span></a>
		</div>

		<form action="?p=subscribers&action=addsubscriber&submit=t" method="post">

		 ' . ($error != '' ? '<p style="color:red;">' . $error . '</p>' : '') . '
		 <p>A subscriber is someone who recieves notifications when pages are updated and reminders when pages haven\'t been updated for a while.</p>
	 	 <table>
		  <tr>
		   <td>What is the subscribers name?</td>
		   <td><input type="text" name="name" /> <br/ ></td>
		  </tr>
		  <tr>
		   <td>What is their email address?</td>
		   <td><input type="text" name="email" /> <br/ ></td>
		  </tr>
		  <tr>
		   <td></td>
		   <td><input type="submit" value="Add Subscriber" /></td>
		  </tr>
		 </table>
		</form>

 		';

		$app->append_content($html);

 	}

 	function add_subscription() {


 		$app = $this->app;
 		$error = '';

 		if (isset($_GET['submit']) && $_GET['submit'] == 't') {

 			$webpage_id = $_POST['webpage_id'];
 			if ($webpage_id == "") {
 				$error = 'You need to select a webpage to subscribe this person to. Did you select a site name by mistake?';
 			} else {
	 			$subscriber_id = $_POST['subscriber_id'];

	 			$sup_exists = $app->dao->query("select sup.* from `subscriptions` sup where sup.`subscriber_id`=:subid AND `webpage_id`=:wpid", "SelectOne", array( ":subid"=>$subscriber_id, ":wpid"=>$webpage_id ) );

	 			//Check if this URL is actually a part of this website
	 			if (isset($sup_exists['id'])) {
	 				$error = 'This subscriber is already subscribed to the selected webpage - select a different web page to subscribe to.';
	 			} else {

	 				$sup_data = array(
	 					":subid"=>$_POST['subscriber_id'],
	 					":webid"=>$_POST['webpage_id'],
	 					":notify"=>(isset($_POST['notify_on_update']) ? $_POST['notify_on_update'] : 0),
	 					":remind"=>(isset($_POST['remind_to_update']) ? $_POST['remind_to_update'] : 0),
	 					":time"=>$_POST['reminder_time']
	 				);

	 				$app->dao->query("INSERT INTO `subscriptions` (`subscriber_id`,`webpage_id`,`notify_on_update`,`remind_to_update`,`reminder_time`) VALUES (:subid, :webid, :notify, :remind, :time) ", "Insert", $sup_data);
	 				$sub_id = $app->dao->insert_id();

	 			}
 			}
 		}

 		$this->view_subscriptions($error);

 	}

 	function view_subscriptions($error = '') {


 		$app = $this->app;
 		$sub_id = $_GET['id'];

 		$subscriber = $app->dao->query("select sub.* from `subscriber` sub where sub.`id`=:sub_id", "SelectOne", array(":sub_id"=>$sub_id));
 		$subscriptions = $app->dao->query("select sup.*, w.`title` as 'webpagetitle', s.`name` as 'sitename' from `subscriptions` sup INNER JOIN (`webpages` w) ON w.`id` = sup.`webpage_id` INNER JOIN (`sites` s) ON s.`id` = w.`site_id` where sup.`subscriber_id`=:sub_id", "SelectAll", array(":sub_id"=>$sub_id));

 		$existing_webpages = array();

 		foreach ($subscriptions as $subp) {
 			$existing_webpages[] = $subp['webpage_id'];
 		}


 		$names = explode(" ", $subscriber['name']);

 		$webpage_opts = '';

 		$sites = $app->dao->query("select s.* from `sites` s", "SelectAll");

 		foreach ($sites as $site) {

	 		$webpages = $app->dao->query("select w.* from `webpages` w WHERE `site_id`=:site order by w.`title` ASC", "SelectAll", array(":site"=>$site['id']));
	 		$webpage_opts .= '<option value="" disabled> -- Webpages for Site: ' . $site['name'] . ' -- </option>';
	 		foreach ($webpages as $w) {

	 		 if (!in_array($w['id'], $existing_webpages)) {
	 		 	$webpage_opts .= '<option value="' . $w['id'] . '">' . $w['title'] . '</option>';
	 		 }

	 		}


 		}

 		$html = '

 		<h2>Subscribers - View Subscriptions</h2>
		 <p>A subscription is where a subscriber is set up to recieve notifications and reminders about a web page.</p>

		<div class="fr">
			<a href="?p=subscribers" class="btn"><span>Back to Overview</span></a>
		</div>

		<table>
		  <tr>
		   <td><b>Subscriber Name: </b></td>
		   <td>' . $subscriber['name'] . '</td>
		  </tr>
		  <tr>
		   <td><b>Email Address:</b></td>
		   <td>' . $subscriber['email'] . '</td>
		  </tr>
		</table>

		<form action="?p=subscribers&action=addsubscription&id=' . $subscriber['id'] . '&submit=t" method="post">
		 <input type="hidden" name="subscriber_id" value="' . $subscriber['id'] . '" />
		 ' . (isset($error) ? '<p style="color:red;">' . $error . '</p>' : '') . '
		 <br />
		 <h3>Add a new subscription</h3>
	 	 <table>
		  <tr>
		   <td>What web page do you want to subscribe them to?</td>
		   <td><select name="webpage_id">' . $webpage_opts . '</select></td>
		  </tr>
		  <tr>
		   <td>Notify ' . $names[0] . ' when the page changes?</td>
		   <td><input type="checkbox" name="notify_on_update" value="1" /> <br/ ></td>
		  </tr>
		  <tr>
		   <td>Remind ' . $names[0] . ' to update the page when it hasn\'t been changed for a while?</td>
		   <td><input type="checkbox" name="remind_to_update" value="1" /> <br/ ></td>
		  </tr>
		  <tr>
		   <td>How many days before we remind them?</td>
		   <td>
		   	<select name="reminder_time">
		   		<option value="1">Daily</option>
				  <option value="7">Weekly</option>
		   		<option value="14">Fortnightly</option>
		   		<option value="30">Monthly (30 Days)</option>
		   		<option value="60">Bi-Monthly (60 Days)</option>
		   		<option value="90">Quarterly (90 Days)</option>
  				<option value="180">Bi-Annualy (180 Days)</option>
  				<option value="365">Annualy (365 Days)</option>
		   	</select> <br/ >
		   </td>
		  </tr>
		  <tr>
		   <td></td>
		   <td>
		    <input type="submit" value="Add Subscription">
		   </td>
		  </tr>
		 </table>
		</form>
		<br />

		<h3>Existing Subscriptions</h3>
		<table width="100%">
			<thead>
				<tr>
					<td>Web Page</td>
					<td>Change Notifications</td>
					<td>Update Reminders</td>
					<td>Reminder Interval</td>
					<td>Last Reminder Sent</td>
					<td>Next Reminder Due</td>
					<td></td>
				</tr>
			</thead>
			<tbody>';

		foreach ($subscriptions as $s) {

			$html .= '
				<tr>
					<td><a href="?p=pages&action=viewpage&id=' . $s['webpage_id'] . '">' . $s['webpagetitle'] . '</a> (' . $s['sitename'] . ')</td>
					<td>' . ($s['notify_on_update'] == 1 ? 'Yes' : 'No') . '</td>
					<td>' . ($s['remind_to_update'] == 1 ? 'Yes' : 'No') . '</td>
					<td>' . $s['reminder_time'] . ' Days</td>
					<td>' . (substr($s['last_reminder_sent'], 0, 4) != 0000 ? date("d M Y", strtotime($s['last_reminder_sent'])) : 'never')  . '</td>
					<td>' . (substr($s['next_reminder_time'], 0, 4) != 0000 ? date("d M Y", strtotime($s['next_reminder_time'])) : 'now') . '</td>
					<td>
						<a href="?p=subscribers&action=deletesubscription&id=' . $s['id'] . '" class="btn"><span>Delete</span></a>
					</td>
				</tr>
			';
		}

		$html .= '</tbody>
			</table>';


		$app->append_content($html);

 	}

 	function delete_subscription() {

 		$id = $_GET['id'];
 		$app = $this->app;

 		$subscription = $app->dao->query("SELECT subscriptions.* from `subscriptions` where `id`=:ID","SelectOne",array(":ID"=>$id));

 		if ($subscription['id'] == $id) {
 			$result = $app->dao->query("DELETE FROM `subscriptions` where `id`=:ID","Delete",array(":ID"=>$id));
 		}

 		$html = '	<h2>Subscribers - View Subscriptions</h2>
		 <p>The subscription was removed.</p>

		<div align="center">
			<a href="?p=subscribers&action=viewsubscriptions&id=' . $subscription['subscriber_id'] . '" class="btn"><span>Return to Subscriber Detais</span></a>
			<a href="?p=subscribers" class="btn"><span>Back to Overview</span></a>
		</div>


		';

		$app->append_content($html);
 	}

  function edit_subscriber() {


     		$app = $this->app;
        $id = $_GET['id'];

     		$error = '';

   			$subscriber = $app->dao->query("select sub.* from `subscriber` sub where sub.`id`=:id", "SelectOne", array(":id"=>$id));

     		if (isset($_GET['submit']) && $_GET['submit'] == 't') {

     			$email = $_POST['email'];

     			$sub_exists = $app->dao->query("select sub.* from `subscriber` sub where sub.`email`=:email", "SelectOne", array(":email"=>$email));

     			//Check if this URL is actually a part of this website
     			if ($sub_exists['id'] !== $subscriber['id']) {
     				$error = 'This email is already in use with another existing subscriber - please use a different email address or check your subscriber doesn\'t already exist..';
     			} else {

     				$page_data = array(
     					":name"=>$_POST['name'],
     					":email"=>$email,
              ":id"=>$subscriber['id'],
              ":isactive"=>1,
     				);

     				$app->dao->query("UPDATE `subscriber` set `name`=:name,`email`=:email,`is_active`=:isactive where `id`=:id", "Insert", $page_data);
     				$sub_id = $app->dao->insert_id();

    		 		$html = '
    		 		<h2>Subscribers - Edit Subscriber</h2>

    		 		<br /><br />
    				<div align="center">

    		 			<h3>We updated your subscriber successfully...YAY!</h3>
    		 			<p><strong><em>What would you like to do next?</em></strong></p>

    					<a href="?p=subscribers&action=viewsubscriptions&id=' . $subscriber['id'] . '" class="btn"><span>View/Edit This Person\'s Subscriptions</span></a>
    					<a href="?p=subscribers&action=addsubscriber" class="btn"><span>Add a new Subscriber</span></a>
              <a href="?p=subscribers" class="btn"><span>Subscriber Overview</span></a>
    				</div>';

    				$app->append_content($html);

     				return;


     			}
     		}


     		$html = '

     		<h2>Subscribers - Edit Subscriber</h2>

    		<div class="fr">
    			<a href="?p=pages" class="btn"><span>Back to Overview</span></a>
    		</div>

    		<form action="?p=subscribers&action=editsubscriber&id=' . $id . '&submit=t" method="post">

    		 ' . ($error != '' ? '<p style="color:red;">' . $error . '</p>' : '') . '
    		 <p>A subscriber is someone who recieves notifications when pages are updated and reminders when pages haven\'t been updated for a while.</p>
    	 	 <table>
    		  <tr>
    		   <td>What is the subscribers name?</td>
    		   <td><input type="text" name="name" value="' . $subscriber['name'] . '"/> <br/ ></td>
    		  </tr>
    		  <tr>
    		   <td>What is their email address?</td>
    		   <td><input type="text" name="email" value="' . $subscriber['email'] . '"/> <br/ ></td>
    		  </tr>
    		  <tr>
    		   <td></td>
    		   <td><input type="submit" value="Update Subscriber" /></td>
    		  </tr>
    		 </table>
    		</form>

     		';

    		$app->append_content($html);
  }

  function delete_subscriber() {

        //subscriber id
     		$id = $_GET['id'];

     		$app = $this->app;

     		$subscriber = $app->dao->query("SELECT subscriber.* from `subscriber` where `id`=:ID","SelectOne",array(":ID"=>$id));

     		if ($subscriber['id'] == $id) {
     			$delete_subsriptions = $app->dao->query("DELETE FROM `subscriptions` where `subscriber_id`=:ID","Delete",array(":ID"=>$id));
          $delete_subsriber = $app->dao->query("DELETE FROM `subscriber` where `id`=:ID","Delete",array(":ID"=>$id));
     		}

     		$html = '	<h2>Subscribers - Delete Subscriber</h2>
    		 <p>The subscriber and their associated subscriptions were removed.</p>

    		<div align="center">
    			<a href="?p=subscribers" class="btn"><span>Back to Overview</span></a>
    		</div>

    		';

    		$app->append_content($html);

  }

 }

?>
