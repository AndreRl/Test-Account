<?php 

define('IN_MYBB', 1); require "./global.php";

if($mybb->get_input('action') != 'statistics' && $mybb->settings['testaccount_statistics'] != 1)
{
	return;
}
	
add_breadcrumb("Test Account - Statistics", "testaccount.php"); 

// Lets check who we're dealing with

$user = get_user($mybb->user['uid']);
if($user['username'] != 'Test')
{
	error_no_permission();
} else if($mybb->settings['testaccount_enable'] != 1)
{
	error_no_permission();
} else if($mybb->settings['testaccount_unlock'] != 1)
{
	error_no_permission();
}

// Lets define some variables

$mybbv = $mybb->version;
$phpv = phpversion();

$databaseengine = $db->short_title;
$dbi = $db->get_version();

 $database = $databaseengine .' ' . $dbi;

eval("\$html = \"".$templates->get("testaccount_statistics")."\";"); 

output_page($html);

?>
