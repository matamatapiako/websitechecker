<?php

class task_check_pages {
	
	function run() {
		
		echo "Running check pages task..";
		
		global $app;
		
		include_once("pages/pages.php");
		
		$p = new page_pages($app);
		
		$sql = "select w.* from `webpages` w WHERE `last_checked` < DATE_ADD(NOW(), INTERVAL -6 HOUR)";
		
		$pages = $app->dao->query($sql);

		foreach ($pages as $page) {
			
			$p->reload_page_content(false, $page['id']);
			
		}
		
		echo "Done!";
		
	}
	
	
}

?>