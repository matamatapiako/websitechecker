<?php

class page_responsetimes {
	
	function page_responsetimes($app) {
		$this->app = $app;
	}
	
	function script() {
		
		
	}
	
	function content() {
		
		$app = $this->app;
		
		if (isset($_GET['disable'])) {
			$html = $this->disable_task();
		}
		
		if (isset($_GET['enable'])) {
			$html = $this->enable_task();
		}
		
		if (isset($_GET['runnow'])) {
			$html = $this->run_task_now();
		}
		
		if (!isset($html)) {
			$html = $this->show_pages_list();
		}
		
		$app->append_content($html);
	}
	
	function show_pages_list() 
	{
	
		
		$app = $this->app;
		
		$joins = "LEFT JOIN (`sites` s) ON s.`id` = w.`site_id` LEFT JOIN (`domains` d) ON w.`domain_id` = d.`id` ";
		$order_by = "order by `title` asc";

		$sql = "SELECT w.*, d.`domain_name`, s.`name` AS 'site_name'  FROM `webpages` w " . $joins . " WHERE w.`monitor_response`=1";
		
		$pages = $app->dao->query($sql, "SelectAll", $qry_data);
		
		$pages_html = '';
		
		foreach ($pages as $p) 
		{
			
			 $latest_resp = $this->get_latest_response_time($p['id']);
			 $avg_resp = $this->get_average_response_times($p['id']);
			 
			 $pages_html .= '<tr>
			 					<td>' . $p['site_name'] . '</td>
			 					<td>' . $p['title'] . '</td>
			 					<td>' . $latest_resp['timechecked'] . '</td>
			 					<td>' . $latest_resp['response_code'] . '</td>
			 					<td>' . round($latest_resp['response_time'], 5) . '</td>
			 					<td>' . $avg_resp['week'] . '</td>
			 					<td>
			 						<a href="?p=responsetimes&report=' . $p['id'] . '" class="btn"><span>View Report</span></a>
			 					</td>
			 				  </tr>';
		}
		
		$html = '
		
		 <h2>Response Times</h2>
		 
		 <div style="float: right;">
		 	<a class="btn" href="?p=tasks"><span>Reload Page</span></a>
		 </div>
		 <h3>Response Times Overview</h3>
		 <table width="100%">
		 	<thead>
		 		<tr>
		 			<td>Site</td>
		 			<td>Page Title</td>
		 			<td>Last Measured</td>
		 			<td>Last Response Code</td>
		 			<td>Last Response Time</td>
		 			<td>7 Days Avg Response</td>
		 			<td></td>
		 		</tr>
		 	</thead>
		 	<tbody>
		 	 ' . $pages_html . '
		 	</tbody>
		 </table>
		 		
		';
		
		return $html;
		
	}
	
	function run_task_now() {
		
		$app = $this->app;
		$id = $_GET['runnow'];
		
		$task = $app->dao->query("select task.* from task where `id`=:ID", "SelectOne", array(":ID"=>$id) );
		
		$app->dao->query("update task set `next_runtime`=NOW() where `id`=:ID", "Update", array(":ID"=>$id) );
		
		$html = '
		 <h2>Tasks</h2>
		 
		 <div style="float: right;">
		 	<a class="btn" href="?p=tasks"><span>Tasks Overview</span></a>
		 </div>
		 <h3>Run Task Now</h3>
		 
		 <p>The task named "' . ucfirst(str_replace("_", " ", $task['taskname'])) . '" has been set to run on the next task run. This should be within the next 5 minutes.</p>
		 <div align="center">
		 	<a class="btn" href="?p=tasks"><span>Continue</span></a>
		 </div>
		';
		
		return $html;
		
	}
	
	function disable_task() {
		
		
		$app = $this->app;
		$id = $_GET['disable'];
		
		$task = $app->dao->query("select task.* from task where `id`=:ID", "SelectOne", array(":ID"=>$id) );
		
		$app->dao->query("update task set `active`=0 where `id`=:ID", "Update", array(":ID"=>$id) );
		
		$html = '
		 <h2>Tasks</h2>
		 
		 <div style="float: right;">
		 	<a class="btn" href="?p=tasks"><span>Tasks Overview</span></a>
		 </div>
		 <h3>Disable Task</h3>
		 
		 <p>The task named "' . ucfirst(str_replace("_", " ", $task['taskname'])) . '" has been disabled and will not run until it has been enabled again.</p>
		 <div align="center">
		 	<a class="btn" href="?p=tasks"><span>Continue</span></a>
		 </div>
		';
		
		return $html;
		
	}
	
	function enable_task() {
		
		
		$app = $this->app;
		$id = $_GET['enable'];
		
		$task = $app->dao->query("select task.* from task where `id`=:ID", "SelectOne", array(":ID"=>$id) );
		
		$app->dao->query("update task set `active`=1 where `id`=:ID", "Update", array(":ID"=>$id) );
		
		$html = '
		 <h2>Tasks</h2>
		 
		 <div style="float: right;">
		 	<a class="btn" href="?p=tasks"><span>Tasks Overview</span></a>
		 </div>
		 <h3>Disable Task</h3>
		 
		 <p>The task named "' . ucfirst(str_replace("_", " ", $task['taskname'])) . '" has been enabled and will run next time the task execution runs after this tasks next run-time.</p>
		 <div align="center">
		 	<a class="btn" href="?p=tasks"><span>Continue</span></a>
		 </div>
		';
		
		return $html;
		
	}
	
	function file_size($file) {
		
		
		 if ( file_exists($file) ) {
		 	
		 	switch (true) {
		 		case filesize($file) < 1000 :
		 			$size = filesize($file) . ' bytes';
		 		break;
		 		case filesize($file) < 1000000 :
		 			$size = ceil(filesize($file)/1000) . ' KB';
		 		break;
		 		case filesize($file) < 1000000000 :
		 			$size = number_format(filesize($file)/1000000, 2) . ' MB';
		 		break;
		 		case filesize($file) < 1000000000000 :
		 			$size = number_format(filesize($file)/1000000000, 2) . ' GB';
		 		break;
		 	}
		 	
		 } else {
		 	$size = 'unknown';
		 }
		 
		 return $size;
		 
	}
	
	function get_latest_response_time($page_id)
	{
		
		$app = $this->app;
		
		$response = $app->dao->query("SELECT * FROM `response` WHERE `page_id`=:page_id ORDER BY `timechecked` DESC", "SelectOne", array(":page_id"=>$page_id));
		
		return $response;
		
	}
	
	function get_average_response_times($page_id)
	{
		
		$app = $this->app;
		
		$response = array();
		
		$week = $app->dao->query("SELECT AVG(`response_time`) as `avg_response` FROM `response` WHERE `page_id`=:page_id and `response_code`=200 AND `timechecked` BETWEEN DATE_ADD(NOW(), INTERVAL -7 DAY) AND NOW()", "SelectOne", array(":page_id"=>$page_id));
		
		$response['week'] = round($week['avg_response'], 5);
		
		return $response;
	}
	
}

?>