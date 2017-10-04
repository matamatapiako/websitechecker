<?php

date_default_timezone_set ("Pacific/Auckland");
include_once("simple_html_dom.php");

session_start();

 class app {

  const page_dir = 'pages/';
  const default_page_id = 'sites';

  function app($run = true) {

   if (!file_exists("config.php"))
   {
     header('location: install.php;');
   }
   
   include_once("config.php");
   $this->public_access = false;
   $this->include_libraries();

   $this->script = '';
   $this->content = '';

   $this->load_pages();
   $this->get_current_page();

   if ($run) {
	$this->run();
   }

  }


  function include_libraries() {

  	include_once("dao.php");
  	include_once("authentication.php");
  	include_once("http.php");
  	include_once("smtp_mailer.php");

  }

  function get_menu() {

	$menu_html = '';
	if ($this->auth->authenticated == false) {
	  	return '';
	}

	if (count($this->pages) != 0) {
		foreach ($this->pages as $p) {
			if ($p['show_on_menu'] == 1) {
				$menu_html .= '<a href="?p=' . $p['name'] . '">' . $p['title'] . '</a>';
			}
		}
	}

	$menu_html .= '<a href="?a=updatepass">Update Password</a>';

	$menu_html .= '<a href="?a=logout">Logout</a>';

	echo $menu_html;
  }

  function load_pages() {


	$this->pages = array();

  	if (isset($this->dao)) {

		$dao = $this->dao;

		$pages = $dao->query("select * from `page`", "SelectAll");
		foreach ($pages as $p) {
			$this->pages[$p['name']] = $p;
		}

	}

  }

  function get_current_page() {

	$this->current_page_id = false;
	$this->current_page = false;

	$this->auth->content();

  	if (isset($_GET['p'])) {
		$this->current_page_id = $_GET['p'];
	} else {
		$this->current_page_id = self::default_page_id;
	}

	if (isset($this->pages[$this->current_page_id])) {
		$this->current_page = $this->pages[$this->current_page_id];
	}

	return $this->current_page;

  }

  function get_script() {

  	$script = '';
  	if (isset($this->script)) { $script .= $this->script; }
  	echo $script;

  }

  function get_content() {

	if ($this->auth->authenticated == false ) {
		if ($this->public_access == false) {
		  	echo $this->auth->get_login_form();
		  	return;
		}
	}

  	$content = '';
  	if (isset($this->content)) { $content .= $this->content; }

  	echo $content;

  }

  function end_page() {
  	$this->page_end = true;
  }

  function append_script($script) {
	  $this->script .= $script;
  }

  function append_content($content) {
  	if (!isset($this->page_end)) {
	  $this->content .= $content;
  	}
  }

  function run() {

	if (is_array($this->current_page)) {

		$page = $this->current_page;
		if (file_exists(self::page_dir . $page['filename'])) {

			include_once(self::page_dir . $page['filename']);
			$page_obj_name = 'page_' . $page['name'];
			if (class_exists($page_obj_name)) {

				$page_obj = new $page_obj_name($this);

				if (method_exists($page_obj, 'script')) {
					$page_obj->script();
				}

				if (method_exists($page_obj, 'content')) {
					$page_obj->content();
				}

				if (isset($page_obj->public_access)) {
					$this->public_access = $page_obj->public_access;
				}

			}

		} else {
			echo "File not found " . self::page_dir . $page['filename'];
		}
	}

  }

  function datefmt($date, $include_time=true) {

	  if (strtotime($date) == 0) {
		  return 'never';
	  }

	  if ($include_time === true) {
		return date("d M Y H:ia", strtotime($date));
	  }

	  return date("d M Y", strtotime($date));

  }

 }


?>
