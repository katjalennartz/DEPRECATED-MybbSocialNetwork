<?php

// Set some useful constants that the core may require or use
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'social_userlist.php');

// Including global.php gives us access to a bunch of MyBB functions and variables
require_once "./global.php";
global $mybb, $db, $lang, $cache, $userpage_parser, $templates, $theme, $headerinclude, $header, $footer, $page, $parser, $username, $uid, $avatar, $userpagelink ;

// Add a breadcrumb
add_breadcrumb('Social Network - Benutzerliste', "social_userlist.php");

$get_user_query = $db->query("SELECT * FROM ".TABLE_PREFIX."users WHERE userpage != ''");
	while($get_user = $db->fetch_array($get_user_query)) { 
		
		$username = format_name(htmlspecialchars_uni($get_user['username']), $get_user['usergroup'], $get_user['displaygroup']);
		$uid = $get_user['uid'];
		$avatar = $get_user['avatar']; 
		$profilelink = get_profile_link($get_user['uid']);
		$userpagelink = '<a href="member.php?action=profile&uid='.$uid.'&area=userpage">social network</a>';

		eval('$row_user .= "' . $templates->get('socialnutzer_row') . '";');
}

	
// Using the misc_help template for the page wrapper
eval("\$page = \"".$templates->get("social_userlist")."\";");

// Spit out the page to the user once we've put all the templates and vars together
output_page($page);

?>