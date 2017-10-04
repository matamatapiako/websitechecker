<?php

class task_cleanup {

	function run() {

		echo "Running cleanup task..";

		global $app;

    $maxruntime = 60; //Max time to run this task for.
    $starttime = time(); //Time that this task started running at
    $version_life = 1; //Days to keep misc versions around for
    chdir(dirname(__FILE__));
    $extract_folder = '..' . DIRECTORY_SEPARATOR . 'extract';
    $site_folders = scandir($extract_folder);

    var_dump($site_folders);

    $bad_dirs = array(".", "..");

    foreach ($site_folders as $site_folder)
    {

      if (!in_array($site_folder, $bad_dirs))
      {
        echo "Site folder " . $site_folder . " scanned..\r\n";

          $version_folders = scandir($extract_folder . DIRECTORY_SEPARATOR . $site_folder);
          foreach ($version_folders as $version_folder)
          {

            echo "Identified version folder " . $version_folder . "..\r\n";

            $filetime = filemtime($extract_folder . DIRECTORY_SEPARATOR . $site_folder . DIRECTORY_SEPARATOR . $version_folder);
            $version = $this->find_version($version_folder);
            $version_age = (time() - $filetime) / 60 / 60 / 24; //in seconds / minutes / hours / days
            if ( $version === false && $version_age > $version_life )
            {

              if (!in_array($version_folder, $bad_dirs))
              {
                //Delete the folder
                shell_exec("rmdir /Q /S " . $extract_folder . DIRECTORY_SEPARATOR . $site_folder . DIRECTORY_SEPARATOR . $version_folder);
                if (!file_exists($extract_folder . DIRECTORY_SEPARATOR . $site_folder . DIRECTORY_SEPARATOR . $version_folder))
                {
                  echo "Deleted folder " . $extract_folder . DIRECTORY_SEPARATOR . $site_folder . DIRECTORY_SEPARATOR . $version_folder . "\r\n";
                }
                else {
                  echo "Failed to delete folder " . $extract_folder . DIRECTORY_SEPARATOR . $site_folder . DIRECTORY_SEPARATOR . $version_folder . "\r\n";
                }
             }

            }

            $runtime = time();

            if (($runtime - $starttime) > $maxruntime)
            {
              echo 'Thats all theres time for folks...' . "\r\n";
              break 2;
            }

          }

      }

    }

		echo "Done!" . "\r\n";

	}

  function find_version($hash)
  {

    global $app;

    $version = $app->dao->query("SELECT `id` from `versions` where `version_hash`=:hash", "SelectOne", array(":hash"=>$hash));

    if ($version !== false)
    {
      return $version['id'];
    }

    //Also check that the content is not the current content of any pages
    $page = $app->dao->query("SELECT `id` from `webpages` where `current_hash`=:hash", "SelectOne", array(":hash"=>$hash));
    if ($page !== false)
    {
      return $page['id'];
    }

    return false;

  }


}

?>
