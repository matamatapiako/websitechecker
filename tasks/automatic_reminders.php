<?php

class task_automatic_reminders {

	function run() {

		echo "Running automatic reminders task..";

		global $app;

		include_once("pages/pages.php");

		$p = new page_pages($app);

		//get subscriptions where current date/time is later than next reminder time

		$sql = "select sup.* from `subscriptions` sup WHERE `next_reminder_time` < NOW()";

		$subscriptions = $app->dao->query($sql);

		$new_reminders = array();

		foreach ($subscriptions as $sup) {

			$webpage = $this->get_webpage($sup['webpage_id']);
			$subscriber = $this->get_subscriber($sup['subscriber_id']);

			if (!isset($new_reminders[$sup['subscriber_id']])) {
				$new_reminders[$sup['subscriber_id']] = array(
					"subscriptions"=>array(),
					"subscriber"=>$subscriber
				);
			}

			$new_reminders[$sup['subscriber_id']]['subscriptions'][] = array("s"=>$sup, "w"=>$webpage);

			//$app->dao->query("")


		}

		foreach ($new_reminders as $rem) {

			$subscriber = $rem['subscriber'];
			$subscriptions = $rem['subscriptions'];

			$names = explode(" ", $subscriber['name']);
			$m = new mailer();
			$to = array("name"=>$subscriber['name'], "address"=>$subscriber['email']);
			$from = array("name"=>app_config::EmailFromName, "address"=>app_config::EmailFromEmail);

			$subject = "Website Checker - Are Your Web Pages Up To Date";

			$body = "Hey " . $names[0] . ",\r\n\r\nAre your webpages up to date? Please check the following page links to see if any updates are required.\r\n\r\n";

			foreach ($subscriptions as $sup) {

				$webpage = $sup['w'];
				$subscription = $sup['s'];

				$sql = "insert into `reminders` (`subscription_id`,`reminder_time`,`webpage_id`,`subscriber_id`,`subscriber_comment`,`subscriber_decision`,`admin_comment`,`status`)";
				$sql .= " VALUES (:supID, NOW(), :webID, :subID, '', '', '', 'new')";

				$data = array(
					":supID"=>$subscription['id'],
					":webID"=>$webpage['id'],
					":subID"=>$subscriber['id']
				);

				$app->dao->query($sql, "Insert", $data);
				$rem_id = $app->dao->insert_id();

				$process_url = app_config::BaseURL . '?p=reminders&action=processreminder&id=' . $rem_id;
				$body .= "'" . $webpage['title'] . "' - " . $process_url . "\r\n\r\n";

				$sql = "Update `subscriptions` set `last_reminder_sent` = NOW(), `next_reminder_time` = DATE_ADD(NOW(), INTERVAL `reminder_time` DAY) where `id`=:supID";
				$app->dao->query($sql, "Update", array(":supID"=>$subscription['id']));

			}

			$body .= "\r\n\r\nBye for Now\r\n\r\nWebsite Checker";

			$m->create_message($to, $from, $subject, $body, 'text/plain');
			
			if (app_config::EmailCCTo != "")
			{
					$m->set_cc(app_config::EmailCCTo);
			}

			$m->send_message();

		}



		echo "Done!";

	}


	function get_webpage($id) {

		global $app;

		$sql = "select w.* from `webpages` w WHERE `id`=:ID";

		$webpage = $app->dao->query($sql, "SelectOne", array(":ID"=>$id) );

		return $webpage;

	}

	function get_subscriber($id) {

		global $app;

		$sql = "select sub.* from `subscriber` sub WHERE `id`=:ID";

		$subscriber = $app->dao->query($sql, "SelectOne", array(":ID"=>$id) );

		return $subscriber;

	}

}

?>
