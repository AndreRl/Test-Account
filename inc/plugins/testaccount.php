<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("What would you say if Die Hard was real life? Yippee ki-yay motherf*cker!");
}
// Password Notice
$plugins->add_hook('admin_config_settings_change', 'testaccount_settings');

// Login Control
$plugins->add_hook('member_do_login_start', 'testaccount_logincheck');

// Avatars & Signatures
$plugins->add_hook('usercp_do_editsig_start', 'testaccount_permission');
$plugins->add_hook('usercp_do_avatar_start', 'testaccount_permission');

// Reputation & PMs
$plugins->add_hook('reputation_start', 'testaccount_reputation');
$plugins->add_hook('private_send_do_send', 'testaccount_permission');

// Emails & Passwords
$plugins->add_hook('usercp_do_email_start', 'testaccount_permission');
$plugins->add_hook('usercp_do_password_start', 'testaccount_permission');

// Posting & Threads
$plugins->add_hook('newreply_do_newreply_start', 'testaccount_permission');
$plugins->add_hook('newthread_do_newthread_start', 'testaccount_permission');

function testaccount_info()
{
	return array(
        "name"  => "Test Account",
        "description"=> "Allows forum administrators to give out a test account with very limited permissions.<br /><br />
		<span style=\"color: red;\">Note: Settings configured here will override usergroup permissions.</span>",
        "website"        => "https://oseax.com",
        "author"        => "Wires <i>(AndreRl)</i>",
        "authorsite"    => "https://oseax.com",
        "version"        => "1.0",
        "guid"             => "",
        "compatibility" => "18*"
	);
}


function testaccount_install()
{
global $db, $mybb;

$setting_group = array(
    'name' => 'testaccount',
    'title' => 'Test Account Settings',
    "description" => "Toggle Test Account features. 
	Note: Settings configured here may override usergroup settings.",
    'disporder' => 1, 
    'isdefault' => 0
);

$gid = $db->insert_query("settinggroups", $setting_group);

$setting_array = array(

    'testaccount_enable' => array(
        'title' => 'Enable Plugin',
        'description' => 'Enable Test Account Plugin along with its features.',
        'optionscode' => 'yesno',
        'value' => 1, 
        'disporder' => 1
    ),

    'testaccount_unlock' => array(
        'title' => 'Unlock Test Account',
        'description' => 'Can the Test account be logged into?',
        'optionscode' => "yesno",
        'value' => 0,
        'disporder' => 2
    ),
	
    'testaccount_posting' => array(
        'title' => 'Post Restriction',
        'description' => 'Can Test account post?',
        'optionscode' => 'yesno',
        'value' => 1,
        'disporder' => 4
    ),
	
    'testaccount_sigavatar' => array(
        'title' => 'Signature & Avatar Restriction',
        'description' => 'Can Test account use Signatures and Avatars?',
        'optionscode' => "select\n0=Signature\n1=Avatar\n2=Both\n3=None",
        'value' => 3,
        'disporder' => 5
    ),
	
    'testaccount_pms' => array(
        'title' => 'Toggle PMs',
        'description' => 'Can Test account use Private Message system?',
        'optionscode' => "yesno",
        'value' => 2,
        'disporder' => 6
    ),
	
    'testaccount_reputation' => array(
        'title' => 'Toggle Reputation',
        'description' => 'Can Test account use Reputation system?',
        'optionscode' => "yesno",
        'value' => 2,
        'disporder' => 7
    ),
);

foreach($setting_array as $name => $setting)
{
    $setting['name'] = $name;
    $setting['gid'] = $gid;

    $db->insert_query('settings', $setting);
}

rebuild_settings();

}

function testaccount_is_installed()
{
    global $mybb;
    if(isset($mybb->settings['testaccount_enable']))
    {
        return true;
    }
    return false;
}

function testaccount_uninstall()
{
global $db;

$db->delete_query('settings', "name IN ('testaccount_enable','testaccount_unlock', 'testaccount_posting', 'testaccount_sigavatar', 'testaccount_pms', 'testaccount_reputation')");
$db->delete_query('settinggroups', "name = 'testaccount'");

rebuild_settings();
}

function testaccount_activate()
{
global $db, $mybb;

$query = $db->simple_select("users", "*", "username = 'test'");
if($db->num_rows($query) != 0)
{
	$db->delete_query("users", "username = 'test'");
}

$boardname = $mybb->settings['bbname'];

$user = array(

	'username'  => 'Test',
	'password'  => '098f6bcd4621d373cade4e832627b4f6',
	'email'     => 'testaccountplugin@'.$boardname.'.com',
	'usergroup' => 2,
	'usertitle' => 'Official Test Account',
	'allownotices' => 1,
	'receivepms' => 1,
	'pmnotice'  => 1,
	'threadmode' => 'linear',
	'showimages' => 1,
	'showvideos' => 1,
	'showsigs'  => 1,
	'showavatars' => 1,
	'showquickreply' => 1,
	'showredirect' => 1,
	
);

$db->insert_query("users", $user);


$testacctemplate = '
<html>
<head>
<title>{$mybb->settings[\'bbname\']} - Test Account Statistics</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="1" cellpadding="4" class="tborder">
<tr>
<td class="thead"><span class="smalltext"><strong>Statistics</strong></span></td>
</tr>
<tr>
<td class="trow1">
MyBB Version: {$mybbv} <br />
PHP Version: {$phpv} <br />
Database: {$database} <br />
</td></tr></table>
{$footer}
</body>
</html>
';

$testaccount_array = array(
    'title' => 'testaccount_statistics',
    'template' => $db->escape_string($testacctemplate),
    'sid' => '-1',
    'version' => '',
    'dateline' => time()
);

$db->insert_query('templates', $testaccount_array);
}

function testaccount_deactivate()
{
global $db, $cache, $gid;

$query = $db->delete_query("users", "username = 'Test'");
// $query = $db->delete_query("usergroups", "gid = $gid");
$db->delete_query("templates", "title IN ('testaccount_statistics')");
$cache->update_usergroups();
}


// Check if Test account can be logged into
function testaccount_logincheck()
{
global $mybb;
	if($mybb->settings['testaccount_enable'] == 1 && $mybb->settings['testaccount_unlock'] == 0)
	{
		if($mybb->get_input('quick_username'))
		{
			$mybb->input['username'] = $mybb->get_input('quick_username');
		}

	

		if($mybb->input['username'] == 'Test' || $mybb->input['username'] == 'test')
		{
			error("Username not allowed.");
			die();
		}
	}
}

// Block change Email & Password 
function testaccount_emailpass()
{
global $mybb, $db, $uid;

if($mybb->get_input('action') == 'do_email' || $mybb->get_input('action') == 'do_password')
{
	if($mybb->user['username'] == 'Test')
	{

		error("You are not able to change your Email or Password.");
	}
}
}

function testaccount_reputation()
{
global $mybb;
if($mybb->settings['testaccount_reputation'] == 0)
{
$mybb->settings['enablereputation'] = 0;

}
}

// Rest of permissions

function testaccount_permission()
{
global $db, $mybb, $uid;

if($mybb->user['username'] == 'Test' && $mybb->settings['testaccount_enable'] == 1)
	{
	$error = 'You are not able to perform this action.';

		switch ($mybb->input['action']) {
			case "do_newreply":
				if($mybb->settings['testaccount_posting'] != 1)
				{
					error($error);
				}
				break;
			case "do_newthread":
				if($mybb->settings['testaccount_posting'] != 1)
				{
					error($error);
				}
				break;
			case "do_editsig":
				if($mybb->settings['testaccount_sigavatar'] != 3 || $mybb->settings['testaccount_sigavatar'] != 1)
				{
					error($error);
				}
				break;
			case "do_avatar":
				if($mybb->settings['testaccount_sigavatar'] != 3 || $mybb->settings['testaccount_sigavatar'] != 0)
				{
					error($error);
				}
				break;
			case "do_send":
				if($mybb->settings['testaccount_pms'] != 1)
				{
					error($error);
				}
				break;
		}
	}
	
}


///////////////////////////////////////////
//////////////////////////////////////////
//            ADMIN SECTION            //
////////////////////////////////////////
///////////////////////////////////////

function testaccount_settings()
{
global $mybb, $db;

// Grab gid
$query = $db->simple_select("settinggroups", "gid", "name = 'testaccount'");
$gid = $db->fetch_field($query, "gid");

if($mybb->get_input('module') == 'config-settings' && $mybb->get_input('action') == 'change' && $mybb->get_input('gid') == $gid)
{
	// Lets check if default password has been changed
	$query = $db->simple_select("users", "*", "username = 'Test'");
	$check = $db->fetch_array($query);
	
	if(empty($check['loginkey']))
	{
		flash_message('Please change Test account\'s password.', 'error');
	}


}


}
