<?php

 require_once("include/app.php");
 $app = new app();
 
?>
<html>
<head>
	<title>Website Checker</title>
	<link rel="stylesheet" href="main.css" />
	<script src="jquery-2.1.4.min.js"></script>
	<script type="text/javascript">
	 <?php $app->get_script(); ?>
	 
	 $("document").ready(function(){
		 
		$("body").on("focus", "#content_preview", function(){
			$("div#content").focus();
		});
		
		$("body").on("click", "#content_preview", function(){
			$("div#content").focus();
		});
		 
	 });
	 
	</script>
	
</head>
<body>

 <div id="menu">
  <?php $app->get_menu(); ?>  
 </div>
 
 <div id="content">
  <?php $app->get_content(); ?>
 </div>

</body>
</html>