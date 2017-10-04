<?php

 $working_dir = dirname(__FILE__);
 if (getcwd() != $working_dir) { var_dump(getcwd()); chdir($working_dir); }
 
 require_once("include/app.php");
 $app = new app(false);
 
 $tasks = $app->dao->query("select * from `task` where `next_runtime` < now() AND `active`=1");
 
 foreach ($tasks as $task) {
	 
	 if (file_exists("tasks/" . $task['taskfile'])) {
		 include_once("tasks/" . $task['taskfile']);
		 $task_obj_name = "task_" . $task['taskname'];
		 if (class_exists($task_obj_name)) {
			 
			 $app->dao->query("update `task` set `status`='running' where `id`=:ID", "Update", array(":ID"=>$task['id']) );
			 
			 $task_obj = new $task_obj_name;
			 $task_obj->run();
			 
			 //update next run time
			 $int = json_decode($task['run_interval'], true);
			 $next_rtime = time();
			 if ($int['days'] != 0) { $next_rtime += ($int['days']*24*60*60); }
			 if ($int['hours'] != 0) { $next_rtime += ($int['hours']*60*60); }
			 if ($int['minutes'] != 0) { $next_rtime += ($int['minutes']*60); }
			 
			 $app->dao->query("update `task` set `status`='idle', `next_runtime`=:RTIME, `last_runtime`=now() where `id`=:ID", 'Update', array(":ID"=>$task['id'],":RTIME"=>date("Y-m-d H:i:s", $next_rtime)));
			 //echo date("Y-m-d H:i:s") . " - " . date("Y-m-d H:i:s", $next_rtime);
			 
		 } else {
			 echo "Class " . $task_obj_name . " not found..";
		 }
	 } else {
		 echo "Cannot find file tasks/" . $task['taskfile'] . "..";
	 }
	 
 }
 
 
?>