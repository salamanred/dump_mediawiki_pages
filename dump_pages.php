<?php

// Configuration ========================

define('DUMP_DIR', 'dump_pages/');  // dump directory
// Database configuration
define('DB_NAME', 'salamanred1');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');
// transformation configuration
define('TRANSFORM', true);   // enable or disable transformation
define('TRANSFORM_DIR', 'transformed_pages/');
define('TRANSFORM_TYPE', 'markdown');
define('TRANSFORM_FILE_EXTENSION', 'md');
define('PANDOC_PATH', 'C:\\Users\\baran1\\AppData\\Local\Pandoc\\');  // pandoc executable path
define('PANDOC_OPTIONS',' --no-wrap ');  // pandoc options. (no-wrap;normalize;...)
// end Configuration =====================


try {
	$dns = 'mysql:host='.DB_HOST.';dbname='.DB_NAME;
	$db = new PDO($dns,DB_USER,DB_PASSWORD);
	$sql = "select * from mw_page where page_namespace = 0";
	
	$pages = $db->query($sql);
	foreach ( $pages as $page ) {

		$sql = "SELECT MAX(rev_id) as max_rev FROM mw_revision where rev_page = ".$page["page_id"];
		$revs = $db->query($sql);
		foreach ( $revs as $max_rev ) {

			$sql = "SELECT rev_text_id FROM mw_revision where rev_id = ".$max_rev["max_rev"];
			$revs_text = $db->query($sql);
			foreach ( $revs_text as $rev_text ) {
				
				$sql = "SELECT old_text FROM mw_text where old_id = ".$rev_text["rev_text_id"];
				$pages_text = $db->query($sql);
				foreach ( $pages_text as $page_text ) {
					if (!is_dir(DUMP_DIR)) mkdir(DUMP_DIR, 0700,true);
					$fh = fopen(DUMP_DIR.'/'.$page["page_title"].'.mw', 'w+');
					// Write the data
					fwrite($fh, $page_text["old_text"]);
					// Close the handle
					fclose($fh);
					echo 'Page extracted: '.$page["page_title"].'</BR>';
					if (TRANSFORM) {
						if (!is_dir(TRANSFORM_DIR)) mkdir(TRANSFORM_DIR, 0700,true);
						//$pandoc_exec = PANDOC_PATH.'pandoc --from=mediawiki --to='.TRANSFORM_TYPE.' --output='.TRANSFORM_DIR.''.$page["page_title"].'.'.TRANSFORM_FILE_EXTENSION.' '.DUMP_DIR.$page["page_title"].'.mw';
						$pandoc_exec = PANDOC_PATH.'pandoc '.PANDOC_OPTIONS.' --from=mediawiki --to='.TRANSFORM_TYPE.' --output='.TRANSFORM_DIR.''.$page["page_title"].'.'.TRANSFORM_FILE_EXTENSION.' '.DUMP_DIR.$page["page_title"].'.mw';
						system($pandoc_exec);
						echo 'Page transformed: '.$page["page_title"].'</BR>';
					}
				}				
			}
		}
	}
} catch (PDOException $e) {
	$log->error($e->getMessage());
	echo 'ERROR: '.$e->getMessage();
	exit();
}


?>