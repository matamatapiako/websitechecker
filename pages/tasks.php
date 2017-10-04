<?php

class page_tasks {
	
	function page_tasks($app) {
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
			$html = $this->show_tasks_list();
		}
		
		$app->append_content($html);
	}
	
	function show_tasks_list() {
	
		
		$app = $this->app;
		
		$tasks = $app->dao->query("select task.* from task");
		
		$tasks_html = '';
		
		foreach ($tasks as $t) {
			 
			 $run_interval = '';
			 $ri = json_decode($t['run_interval'], true);
			 if (isset($ri['days']) && $ri['days'] != 0) { $run_interval .= $ri['days'] . " day" . ($ri['days'] > 1 ? "s" : "") . ", ";  }
			 if (isset($ri['hours']) && $ri['hours'] != 0) { $run_interval .= $ri['hours'] . " hour" . ($ri['hours'] > 1 ? "s" : "") . ", ";  }
			 if (isset($ri['minutes']) && $ri['minutes'] != 0) { $run_interval .= $ri['minutes'] . " minute" . ($ri['minutes'] > 1 ? "s" : "") . ", ";  }
			 $run_interval = "Every " . rtrim($run_interval, ", ");
			 
			 $tasks_html .= '<tr>
			 					<td>' . ucfirst(str_replace("_", " ", $t['taskname'])) . '</td>
			 					<td>' . $t['description'] . '</td>
			 					<td>' . $run_interval . '</td>
			 					<td>' . $t['last_runtime'] . '</td>
			 					<td>' . $t['next_runtime'] . '</td>
			 					<td>' . ($t['active'] == 1 ? 'Yes' : 'No') . '</td>
			 					<td>' . ucfirst($t['status']) . '</td>
			 					<td>
			 						' . ($t['active'] == 1 ? '<a href="?p=tasks&disable=' . $t['id'] . '" class="btn"><span>Disable</span></a>' : '') . '
			 						' . ($t['active'] == 0 ? '<a href="?p=tasks&enable=' . $t['id'] . '" class="btn"><span>Enable</span></a>' : '') . '
			 						<a href="?p=tasks&runnow=' . $t['id'] . '" class="btn"><span>Run Now</span></a>
			 					</td>
			 				  </tr>';
		}
		
		$html = '
		
		 <h2>Tasks</h2>
		 
		 <div style="float: right;">
		 	<a class="btn" href="?p=tasks"><span>Reload Page</span></a>
		 </div>
		 <h3>Apps Overview</h3>
		 <table width="100%">
		 	<thead>
		 		<tr>
		 			<td>Task Name</td>
		 			<td>Description</td>
		 			<td>Run Interval</td>
		 			<td>Last Run Time</td>
		 			<td>Next Run Time</td>
		 			<td>Enabled</td>
		 			<td>Status</td>
		 			<td></td>
		 		</tr>
		 	</thead>
		 	<tbody>
		 	 ' . $tasks_html . '
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
	
}

?>