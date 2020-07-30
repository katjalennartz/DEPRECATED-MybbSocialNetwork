<?php

/**
 * MyXBL
 *
 * @author euantor <admin@xboxgeneration.com>
 * @version 1.0
 * @copyright euantor 2011
 * @package MyXBL
 * 
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Modifiziert von risuena (http://lslv.de - http://storming-gates.de/member.php?action=profile&uid=39 ) zum social network
 * Support zum social network: http://storming-gates.de/showthread.php?tid=14707
 */

//fehleranzeige
// error_reporting ( -1 );
// ini_set ( 'display_errors', true ); 

 // Disallow Direct Access
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/*
*	Plugin Information
*/
function userpages_info() {
    global $lang;
    
    $lang->load("userpages");
    
    return array(
        "name" => $lang->userpages_title." - erweitert zum social network",
        "description" => $lang->userpages_desc." Erweitert von <a href=\"http://lslv.de/\" target=\"_blank\">risuena zum social network.</a>",
        "website" => "",
		"author" => "euantor / Codicious",
        "authorsite" => "",
        "version" => "1.3",
		"guid" => "a777fe64a45ccf1a7f6e5af692f2480a",
		"compatability" => "16*, 18*"        
    );   
}
/*
*	End Plugin Information
*/

/*
*	Plugin Install
*/
function userpages_install() {
    global $mybb, $db, $cache, $templates;
	
	//Erstellt Tabelle für die Freunde
    $db->write_query("CREATE TABLE `".TABLE_PREFIX."socialfriends` (
    `lid` int(20) NOT NULL AUTO_INCREMENT,
  	`uid` int(20) NOT NULL,
  	`isfriend` int(20) NOT NULL,
  	`username` varchar(200) NOT NULL,
  	`accepted` int(1) NOT NULL,
  	`asked` int(20) NOT NULL,
  	PRIMARY KEY (`lid`)
	) ENGINE=InnoDB");

	//Erstellt Tabelle für die Antworten
     $db->write_query("CREATE TABLE `".TABLE_PREFIX."socialantwort` (
		`social_aid` int(20) NOT NULL AUTO_INCREMENT,
		`social_id` int(20) NOT NULL,
		`userpageid` int(20) NOT NULL,
  		`social_date` datetime NOT NULL,
  		`social_uid` int(20) NOT NULL,
  		`antwort` varchar(300) NOT NULL,
  		`del_username` varchar(300) DEFAULT NUll,
  		`del_nutzername` varchar(300) DEFAULT NUll,
  		`del_profilbild` varchar(30) DEFAULT NUll,
  		PRIMARY KEY (`social_aid`),
  		UNIQUE KEY `social_aid` (`social_aid`),
  		KEY `social_id` (`social_id`)
		) ENGINE=InnoDB");
	
	//Erstellt Tabelle für die Posts
    $db->write_query("CREATE TABLE `".TABLE_PREFIX."socialpost` (
		`social_id` int(20) NOT NULL AUTO_INCREMENT,
		`uid` int(20) NOT NULL,
  		`avatar` varchar(200) NOT NULL,
  		`social_date` datetime NOT NULL,
  		`userpageid` int(20) NOT NULL,
  		`social_post` varchar(300) NOT NULL,
  		`del_username` varchar(300) DEFAULT NUll,
  		`del_nutzername` varchar(300) DEFAULT NUll,
  		`del_profilbild` varchar(30) DEFAULT NUll,
  		PRIMARY KEY (`social_id`)
		) ENGINE=InnoDB");

	//Erstellt Tabelle für Likes.
 	$db->write_query("CREATE TABLE `".TABLE_PREFIX."sociallikes` (
  		`id` int(11) NOT NULL AUTO_INCREMENT,
  		`postid` int(11) NOT NULL,
  		`antwortid` int(11) NOT NULL,
  		`user` varchar(200) NOT NULL,
  		`uid` int(11) NOT NULL,
  		PRIMARY KEY (`id`)
		) ENGINE=InnoDB");

	// fuegt der tabelle users und usergroup weitere attribute hinzu (userpage, social_titelbild, social_profilbild, social_friendcheck, social_postcheck, social_likecheck, social_namedcheck, social_nutzername )
    $db->write_query("ALTER TABLE `".TABLE_PREFIX."users` ADD `userpage` TEXT NOT NULL, ADD `social_titelbild` VARCHAR(300), ADD `social_profilbild` VARCHAR(300), ADD `social_friendcheck` INT(1), ADD `social_postcheck` INT(1), ADD `social_likecheck` INT(1), ADD `social_namedcheck` INT(1), ADD `social_nutzername` VARCHAR(30);");
    $db->write_query("ALTER TABLE `".TABLE_PREFIX."usergroups` ADD `canuserpage` INT(1) NOT NULL DEFAULT '0', ADD `canuserpageedit` INT(1) NOT NULL DEFAULT '0', ADD `canuserpagemod` INT(1) NOT NULL DEFAULT '0';");
	
    $db->write_query('UPDATE '.TABLE_PREFIX.'usergroups SET canuserpage = 1 WHERE canusercp = 1');
    $db->write_query('UPDATE '.TABLE_PREFIX.'usergroups SET canuserpageedit = 1, canuserpagemod = 1 WHERE gid IN (2, 3, 4, 6)');
	
    $cache->update_usergroups();
}
/*
*	End Plugin Install
*/

/*
*	Check if plugin is installed
*/
function userpages_is_installed() {
    global $db;
    return $db->field_exists("userpage", "users");
}
/*
*	End Check if plugin is installed
*/

/*
*	Plugin Activate
*/
function userpages_activate() {
    global $db, $mybb, $lang;
    
    $lang->load("userpages");
	
    $settings_group = array(
        "gid" => "",
        "name" => "userpages",
        "title" => $lang->userpages_settings_title,
        "description" => $lang->userpages_settings_desc,
        "disporder" => "0",
        "isdefault" => "0",
    );
    
    $db->insert_query("settinggroups", $settings_group);
    $gid = $db->insert_id();
	
    $setting[0] = array(
	    "name" => "userpages_html_active",
	    "title" => $lang->userpages_html_active,
	    "description" => $lang->userpages_html_active_desc,
	    "optionscode" => "yesno",
	    "value" => "0",
	    "disporder" => "1",
	    "gid" => $gid,
	);
	
    $setting[1] = array(
	    "name" => "userpages_mycode_active",
	    "title" => $lang->userpages_mycode_active,
	    "description" => $lang->userpages_mycode_active_desc,
	    "optionscode" => "yesno",
	    "value" => "1",
	    "disporder" => "2",
	    "gid" => $gid,
	);
	
    $setting[2] = array(
	    "name" => "userpages_images_active",
	    "title" => $lang->userpages_images_active,
	    "description" => $lang->userpages_images_active_desc,
	    "optionscode" => "yesno",
	    "value" => "1",
	    "disporder" => "3",
	    "gid" => $gid,
	);
	
	$setting[3] = array(
	    "name" => "userpages_badwords_active",
	    "title" => $lang->userpages_badwords_active,
	    "description" => $lang->userpages_badwords_active_desc,
	    "optionscode" => "yesno",
	    "value" => "1",
	    "disporder" => "4",
	    "gid" => $gid,
	);
	
	$setting[4] = array(
	    "name" => "userpages_videos_active",
	    "title" => $lang->userpages_videos_active,
	    "description" => $lang->userpages_videos_active_desc,
	    "optionscode" => "yesno",
	    "value" => "1",
	    "disporder" => "5",
	    "gid" => $gid,
	);
	
	foreach ($setting as $row) {
	    $db->insert_query("settings", $row);
	}
    rebuild_settings();
    
    $template[0] = array(
		"title" => 'userpages_content',
		"template"	=> '<html>
	<head>
		<title>{$mybb->settings[\\\'bbname\\\']} - {$lang->viewinguserpage}</title>
		{$headerinclude}
		<link type="text/css" rel="stylesheet" href="social.css" />
	</head>
	<body>
		{$header}

		<table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
			<thead>
				<tr>
					<td class="thead"><strong>{$lang->viewinguserpage}</strong></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="tcat">
						{$memprofile[\\\'view_full_profile\\\']}
					</td>
				</tr>
				<tr>
					<td class="trow1">
						<div class="social_rahmen_ganzaussen">
						<div class="social_titelbild" style="background-image: url({$titelbild});">
						<div class="social_profilbild"><img src="{$profilbild}" width="150px" height="150px" alt="profilbild"/></div>
						<div class="social_name">{$nutzername} {$gotoedit}</div>
						<br style="clear:both;"/>
							<table class="social_tabellefriendpost">
								<tr>
									<td valign="top" class="social_friendrow" align="center">
										<div class="social_friendinfobox">
										<div class="social_infobox">
											<div style="text-align:center;"><img src="social/logo_150px.png" alt="logo"/></div>
											{$memprofile[\\\'userpage\\\']}
										</div>
										</div> 
									{$social_addfriends} {$social_delfriends}
											
									<table width="240px" style="padding-left:15px;">
									{$social_friends_title}
									{$social_friends}
									{$social_friendtoadd_title}
									{$social_wannafriends}
									{$social_friendsiaksed_title}
									{$social_friendsiasked}
									</table>
									</td>
									<td valign="top">
												{$social_postform}
												{$social_page}
									</td>
									</tr>
									<tr>
									<td align="right" colspan="2"><a href="http://lslv.de/" target="_blank" style="font-size:7pt;">social network by risuena</a></td>
									</tr>
									</table>	
					</td>
					</tr>
				</table>
			</td>
			</tr>
			</tbody>
		</table>
		{$footer}
	</body>
</html>',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
    $template[1] = array(
		"title" => 'userpages_usercp_main',
		"template"	=> '<html>
	<head>
		<title>{$mybb->settings[\\\'bbname\\\']} - {$lang->changeuserpage}</title>
		{$headerinclude}
	</head>
	<body>
		{$header}
		<table width="100%" border="0" align="center">
			<tr>
				{$usercpnav}
				<td valign="top">
					<form method="post" action="usercp.php">
						<table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
							<tr>
								<td class="thead" colspan="2">
								<div align="center"><span class="smalltext"><strong><img src="social/logo_300px.png" alt="logo"/></strong></span>
								<br /> {$userpagelink}
								</div>	
								</td>
							</tr>
							<tr>
								<td class="trow2">
									<b>Benachrichtigungen?</b><br />
									<input type="checkbox" name="pn_friend" {$check_friend}> bei Freundschaftsanfragen.<br />
									<input type="checkbox" name="pn_post" {$check_post}> bei neuem Post oder Antwort.<br />
									<input type="checkbox" name="pn_like" {$check_likes}> bei Likes.<br />
									<input type="checkbox" name="pn_named" {$check_named}> wenn man in einem Post erwähnt wurde.<br/>
									<br />
									Nutzername [nur Text - keine \\\' oder andere Sonderzeichen] - kann leergelassen werden: <br />
									<input type="text" name="nutzername" value="{$nutzername}"/><br />
									Profilbild [nur URL] 150px * 150px: <br />
									<input type="text" name="profilbild" value="{$profilbild}"/><br />
									Titelbild [nur URL] 800px * 200px: <br />
									<input type="text" name="titelbild" value="{$titelbild}"/><br/><br />

									Bitte nach folgendem Schema ausfüllen:<br />
									<p style="padding-left:20px;"> ist [b]X[/b] Jahre alt.<br />
										arbeitet in/als [b]X[/b].<br />
										wohnt in [b]Stadt[/b].<br />
										geboren in [b]X[/b]<br />
										ist single. / Es ist kompliziert. / Ist in einer Beziehung mit [url=member.php?action=profile&uid=<b><i>USERID</i></b>&area=userpage]<i><b>USERNAME</b></i>[/url]<br /><br /></p>
									<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
									<input type="hidden" name="action" value="edituserpage_do" />
									<textarea name="userpage_content" id="userpage_content" rows="10" cols="50">{$currentuserpage}</textarea>
								
								</td>
							</tr>
							<tr>

							</tr>
						</table>
						<br />
						<div align="center">
							<input type="submit" value="{$lang->saveuserpage}" name="{$lang->saveuserpage}" class="button" />
						</div>
					</form>
				</td>
			</tr>
		</table>
		{$footer}
	</body>
</html>',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
	$template[2] = array(
		"title" => 'userpages_usercp_nav',
		"template"	=> '<tr><td class="trow1 smalltext"><a href="usercp.php?action=edituserpage"><b>Social Network</b></a></td></tr>',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
	$template[3] = array(
		"title" => 'userpages_modcp_main',
		"template"	=> '<html>
	<head>
		<title>{$mybb->settings[\\\'bbname\\\']} - {$lang->userpages_modcp}</title>
		{$headerinclude}
	</head>
	<body>
		{$header}
		<table width="100%" border="0" align="center">
			<tr>
				{$modcp_nav}
				<td valign="top">
					{$multipage}
					<table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
						<tr>
							<td class="thead" colspan="3"><strong>{$lang->userpages_modcp}</strong></td>
						</tr>
						<tr>
							<td class="tcat"><strong>{$lang->username}</strong></td>
							<td class="tcat" colspan="2" align="center"><strong>{$lang->action}</strong></td>
						</tr>
						{$userpages_users}
					</table>
					{$multipage}
				</td>
			</tr>
		</table>
		{$footer}
	</body>
</html>',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
	$template[4] = array(
		"title" => 'userpages_modcp_singleuser',
		"template"	=> '<tr>
	<td class="{$altbg}">
		<a href="{$user[\\\'edituserpagelink\\\']}" title="{$lang->edituserpage}">{$user[\\\'username\\\']}</a>
	</td>
	<td class="{$altbg}" align="center">
		<a href="{$user[\\\'viewuserpagelink\\\']}">{$lang->viewuserpage}</a>
	</td>
	<td class="{$altbg}" align="center">
		<a href="{$user[\\\'edituserpagelink\\\']}">{$lang->edituserpage}</a>
	</td>
</tr>',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
	$template[5] = array(
		"title" => 'userpages_modcp_modify',
		"template"	=> '<html>
	<head>
		<title>{$mybb->settings[\\\'bbname\\\']} - {$lang->userpages_modcp_modify}</title>
		{$headerinclude}
	</head>
	<body>
		{$header}
		<table width="100%" border="0" align="center">
			<tr>
				{$modcp_nav}
				<td valign="top">
					<form method="post" action="modcp.php">
						<table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
							<tr>
								<td class="thead" colspan="2"><strong>{$lang->userpages_modcp_modify}</strong></td>
							</tr>
							<tr>
								<td class="trow2">
									<b>Benachrichtigungen?</b><br />
									<input type="checkbox" name="pn_friend" {$check_friend}> bei Freundschaftsanfragen.<br />
									<input type="checkbox" name="pn_post" {$check_post}> bei neuem Post oder Antwort.<br />
									<input type="checkbox" name="pn_like" {$check_likes}> bei Likes.<br />
									<input type="checkbox" name="pn_named" {$check_named}> wenn man in einem Post erwähnt wurde.<br/>
									
									Profilbild [nur URL]: <br />
									<input type="text" name="profilbild" value="{$profilbild}"/><br />
									Titelbild [nur URL]: <br />
									<input type="text" name="titelbild" value="{$titelbild}"/><br/><br />
									
									
									Bitte nach folgendem Schema ausfüllen:<br />
									<p style="padding-left:20px;"> X Jahre alt.<br />
										arbeitet in/als X.<br />
										wohnt in Las Vegas.<br />
										geboren in X<br />
										ist single. / Es ist kompliziert. / Ist in einer Beziehung mit <br /><br /></p>
									<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
									<input type="hidden" name="action" value="userpages_edit_do" />
									<input type="hidden" name="uid" value="{$uid}" />
									<textarea name="userpage_content" id="userpage_content" rows="20" cols="70">{$content[\\\'userpage\\\']}</textarea>
									
								</td>
							</tr>
						</table>
						<br />
						<div align="center">
							<input type="submit" value="{$lang->saveuserpage}" name="{$lang->saveuserpage}" class="button" />
						</div>
					</form>
				</td>
			</tr>
		</table>
		{$footer}
	</body>
</html>',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
	$template[6] = array(
		"title" => 'userpages_modcp_nav',
		"template"	=> '<tr>
		<td class="tcat">
			<div class="float_right"><img src="{$theme[\\\'imgdir\\\']}/collapse{$collapsedimg[\\\'modcpuserpages\\\']}.gif" id="modcpuserpages_img" class="expander" alt="[-]" title="[-]" /></div>
			<div><span class="smalltext"><strong>{$lang->userpages_modcp}</strong></span></div>
		</td>
	</tr>
	<tbody style="{$collapsed[\\\'modcpuserpages_e\\\']}" id="modcpuserpages_e">
		<tr><td class="trow1 smalltext"><a href="modcp.php?action=userpages" class="modcp_nav_item modcp_nav_editprofile">{$lang->moderate_userpages}</a></td></tr>
	</tbody>
',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
		$template[7] = array(
		"title" => 'social_post',
		"template"	=> '<form action="" method="post">
<table class="social_posttabelle" border="0" cellpading="1" cellspacing="1">
	<tr>
		<td>
			<table border="0" class="social_tabellepostrahmen"  cellpading="5" cellspacing="5">
				<tr>
					<td width="80px" rowspan="3" valign="top"  class="social_post_td_profilbild">
						<a name="{$postid}" id="{$postid}"></a>
						<img src="{$avatar}" width="80" alt="ava" /></td>
					<td valign="top" class="social_usernametd">{$username} - <input type="submit" name="loeschen" value="löschen" class="social_text_button" />
					    <input type="hidden" name="postid" value="{$postid}"/>
      					<input type="hidden" name="postautorid" value="{$postautorid}"/>
						<input type="hidden" name="anzahluserlikepost" value="{$anzahl_likesantwort}"/>
      					</form>
					</td>
				</tr>
				<tr>
					<td class="social_tabellepostrahmen" valign="top"><span class="smalltext">{$social_datum}</span></td>
				</tr>
				<tr>
					<td class="social_tabellepostrahmentd" valign="top">{$post}</td>
				</tr>
				<tr>
				<td colspan="2" align="right">
						<tooltip class="userlike">{$anzahl_likespost} likes<{$classbox}>{$social_likeuser_tpl}</{$classbox}></tooltip>
						<input type="submit" name="like" value="{$var_likepost}" class="{$var_likepost}"></td>
				</tr>
				<tr>
					<td class="social_tabellepostrahmentd" colspan="2">
						<table class="social_anttabellerahmen" width="100%">
							<tr>
								<td width="80px"></td>
								<td>{$social_antwort}</td>
							</tr>
							<tr>
								<td width="80px"></td>
								<td colspan="2" align="center">
										<form action="" method="post">
										<input type="hidden" name="ant_date" value="submit">
										<table border="0" cellspacing="0" class="social_antsetzen">
											<tr>
												<td align="left">  
													<input type="text" name="ant_day" size="2" maxsize="2" value="01">
												</td>
												<td align="left">  
													<input type="text" name="ant_month" size="2" maxsize="2" value="01">
												</td>
												<td  align="left"  >  
													<input type="text" name="ant_year" size="4" maxsize="4" value="2013">
  												</td>
												<td width="16%" rowspan="2"></td>
												<td align="left">  
													<input type="text" name="ant_std" size="2" maxsize="2" value="13">
												</td>
												<td align="left">  
													<input type="text" name="ant_min" size="2" maxsize="2" value="02">
												</td>
												<td  align="left"  >  
													<input type="text" name="ant_sek" size="2" maxsize="2" value="22">
  												</td>
											</tr>
											<tr>
												<td colspan="7" align="center">
													<textarea name="antwort" class="social_socialpostantwort" rows="1">Antwort</textarea><br />
													<input type="hidden" name="get_socialid" value="{$postid}"/>
													<input type="submit" name="ant_senden" value="absenden"/>
													</form>
												</td>
											</tr>
										</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
	$template[8] = array(
		"title" => 'social_addfriends',
		"template"	=> '<form action="" method="post">
			<input type="submit" name="addfriend" value="Freund +"/>
			</form>
',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
	$template[9] = array(
		"title" => 'social_antwort',
		"template"	=> '<form action="" method="post"> 
			<table class="social_anttabellerahmen" cellspacing="2" cellpadding="2">
				<tr>
					<td width="80px" rowspan="3" valign="top">
						<a name="a{$ant_id}" id="a{$ant_id}"></a>
						<img src="{$ant_avatar}" width="80" alt="avatar" /></td>
					<td valign="top" style="white-space: nowrap;">{$ant_username} - <input type="submit" name="aloeschen" value="löschen" class="social_text_button">
					</td>
				</tr>
				<tr>
					<td valign="top">{$ant_datum}</td>
				</tr>
				<tr>
					<td valign="top">{$ant_antwort}</td>
				</tr>
				
				<tr>
					<td colspan="2" align="right">
						<tooltip class="userlike">{$anzahl_likesantwort} likes<{$classbox}>{$social_likeuser_tpl}</{$classbox}></tooltip>
						<input type="submit" name="likeantwort" value="{$var_like}" class="{$var_like}"></td>
				</tr>
			</table>
<input type="hidden" name="l_aid" value="{$ant_id}"/>
<input type="hidden" name="antautorname" value="{$antautor}"/>
</form>
',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);	

	$template[10] = array(
		"title" => 'social_delanfrage',
		"template"	=> '<input type="submit" name="delanfrage" value="zuruecknehmen" />
',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);

	$template[11] = array(
		"title" => 'social_delfriendownpage',
		"template"	=> '<input type="submit" name="delfromownpage" value=" - " /></form>
',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
	$template[12] = array(
		"title" => 'social_delfriends',
		"template"	=> '<form action="" method="post">
							<input type="submit" name="delfriend" value="Freund -"/>
						</form>
',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
	$template[13] = array(
		"title" => 'social_friends',
		"template"	=> '<tr>
	<td width="40px"><img src="{$friendava}" width="40px" alt="avatar" /></td> 
	<td valign="middle" align="left"><a href="member.php?action=profile&uid={$friendid}&area=userpage">{$friendname}</a>
		<form action="" method="post">
		<input type="hidden" name="get_friendid" value="{$friendid}" />
		{$social_delfriendownpage}
		</form>
	</td>
</tr>
',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);

	$template[14] = array(
		"title" => 'social_page',
		"template"	=> '<div class="social_rahmen_aussen">
						{$social_post}
						</div>
',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
	$template[15] = array(
		"title" => 'social_postform',
		"template"	=> '<div class="social_poststatus">
    <form action="" method="post">
	<table class="social_poststatustab">
       <tr>
		   <td align="left" width="25px">   
            <input type="text" name="socialpost_day" size="2" maxsize="2" value="01">
        	</td>
        	<td align="left" width="25px">  
            <input type="text" name="socialpost_month" size="2" maxsize="2" value="01">
         	</td>
          	<td align="left" width="25px"><input type="text" name="socialpost_year" size="4" value="2013">
			<input type="hidden" name="socialpost_date" value="submit">
        	</td>
		    <td align="left" width="25px">  </td>
			<td align="left" width="25px">  
			<input type="text" name="std" size="2" maxsize="2" value="13">
			</td>
			<td align="left" width="25px">   
			<input type="text" name="min" size="2" maxsize="2" value="02">
			</td>
			<td align="left" width="25px">    
			<input type="text" name="sek" size="2" maxsize="2" value="22">
  			</td>
  			<td rowspan="2"></td>
		</tr>
		<tr>
			<td colspan="7">
			<textarea name="socialpost" class="social_socialpostinput" rows="3">Beitrag</textarea><br />
			<input type="submit" name="senden" value="absenden"/><br/>
			</td>
		</tr>
        </table>	
		</form>
</div>
',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
	$template[16] = array(
		"title" => 'social_wannafriends',
		"template"	=> '<tr>
	<td valign="bottom" colspan="2"><a href="member.php?action=profile&uid={$friendid}&area=userpage">{$friendname}</a></td>
</tr>
<tr>
	<td colspan="2" valign="top">will dein Freund sein
		<form action="" method="post">
		<input type="hidden" name="wannabe" value="{$friendid}"/>
		<input type="submit" name="accept" value="add"/>
		<input type="submit" name="deny" value="deny"/>
		</form></td>
</tr>
',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);

	$template[17] = array(
		"title" => 'social_friendsiasked',
		"template"	=> '<tr>
	<td valign="bottom" colspan="2">
	<a href="member.php?action=profile&uid={$friendid}&area=userpage">{$friendname}</a> </td>
</tr>
<tr>
	<td colspan="2" valign="top"><form action="" method="post">
		<input type="hidden" name="get_friendid" value="{$friendid}"/>{$social_delanfrage}</form></td>
</tr>
',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
$template[18] = array(
	"title" => 'social_likeuser_tpl',
	"template"	=> '{$users}<br />
		',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
$template[19] = array(
		"title" => 'social_newsfeed',
		"template"	=> '<html>
						<head>
							<title>Newsfeed</title>
							{$headerinclude}
						<link type="text/css" rel="stylesheet" href="social.css" />
						</head>
						<body>
							{$header}
						<center>
						<div class="nf_divaussen">
							<center>
							{$social_newsfeedpost}
							<br />
						</center>
						</div>
						</center>
						{$footer}
						</body>
						</html>
',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
$template[20] = array(
		"title" => 'social_newsfeedant',
		"template"	=> '<tr>
		<td valign="top"><form action="" method="post">
		<input type="hidden" name="l_aid" value="{$ant_id}"/>
		<input type="hidden" name="antautorname" value="{$antautor}"/>
			<table class="nf_antwortgesamt">
				<tr>
					<td class="nf_tdantwortava" valign="top">
						<img src="{$ant_avatar}" width="50px" alt="avatar" />
					</td>
					<td valign="top">
						<table class="table_nfantwortwrap">
							<tr>
								<td valign="top">{$ant_username} - <span class="smalltext">{$ant_datum}</span></td>
							</tr>
							<tr>
								<td valign="top">{$ant_antwort}</td>
							</tr>
							<tr>
								<td align="right" valign="top" class="tdlike">
									<tooltip class="userlike">{$anzahl_likesantwort} likes
											<{$classbox}>{$social_likeuser_tpl}</{$classbox}>
									</tooltip>
									<input type="submit" name="likeantwort" value="{$var_like}" class="{$var_like}">
								<hr  class="hr_nf">
									</form>
								</td>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
	<td>
</tr>
',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
	$template[21] = array(
		"title" => 'social_newsfeedpost',
		"template"	=> '<form action="" method="post"> 
<input type="hidden" name="postid" value="{$postid}"/>
<input type="hidden" name="postautorid" value="{$postautorid}"/>
<input type="hidden" name="anzahluserlikepost" value="{$anzahl_likesantwort}"/>
<br />
<table class="table_nfposts">
	<tr>
		<td class="td_nfpostava" valign="top"><img src="{$avatar}" width="80px" alt="avatar"/><br /><span class="smalltext"><a href="member.php?action=profile&uid={$userpageid}&area=userpage#{$postid}">Zum Post</a></span></td>
		<td class="td_nfpost" valign="top">
			<table class="table_nfpostwrap">
				<tr>
					<td valign="top">{$username} - <span class="smalltext">{$social_datum}</span></td>
				</tr>
<!--				<tr>
					<td valign="top"></td>
				</tr>--> 
				<tr>
					<td valign="top" >{$post} </td>
				</tr>
				<tr>
					<td align="right" valign="top" class="tdlike">
						<tooltip class="userlike">
							{$anzahl_likespost} likes
								<{$classbox}>{$social_likeuser_tpl}</{$classbox}>
						</tooltip>
						<input type="submit" name="like" value="{$var_likepost}" class="{$var_likepost}">
					</td>
				</tr>
				<tr>
					<td  valign="top">
						<table class="table_nfantwort">
							<tr>
								<td valign="top">{$social_newsfeedant}</td>
							</tr>
							<tr>
								<td align="left">		
										
											<span style="padding-left:38px;">&nbsp;</span>
											<span class="nf_date">	
											<input type="hidden" name="ant_date" value="submit">
											<input type="text" name="ant_day" size="2" maxsize="2" value="01">.<input type="text" name="ant_month" size="2" maxsize="2" value="01">.<input type="text" name="ant_year" size="4" maxsize="4" value="2013">
										</span>
											<span class="nf_datetimeabstand">&nbsp;</span>
											<span class="nf_time">
											<input type="text" name="ant_std" size="2" maxsize="2" value="13">:<input type="text" name="ant_min" size="2" maxsize="2" value="02">:<input type="text" name="ant_sek" size="2" maxsize="2" value="22">
										</span><br />
											<img src="{$ownavatar}" width="40px" alt="avatar" />
											<textarea name="antwort" class="nf_textarea">Antwort</textarea><br />
											<input type="hidden" name="get_social" value="{$postid}"/>
											<input type="hidden" name="get_userpageid" value="{$userpageid}"/>
											<input type="submit" name="ant_senden" value="absenden"/>
								
								</td>
							</tr>
						</table>
					</td> 
				</tr>
			</table>
		</td>
	</tr>
</table></form>
',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
	$template[22] = array(
		"title" => 'social_userlist',
		"template"	=> '<html>
	<head>
		<title>{$mybb->settings[\\\'bbname\\\']} - Social Network Nutzer</title>
		{$headerinclude}
		<link type="text/css" rel="stylesheet" href="social.css" />
	</head>
	<body>
		{$header}

		<table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
			<thead>
				<tr>
					<td class="thead"><strong>Social Network Nutzer</strong></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="tcat" align ="center" valign="middle">
						{$row_user}
					</td>
				</tr>
				<tr>

			</td>
			</tr>
			</tbody>
		</table>
		{$footer}
	</body>
</html>',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);
	
		$template[23] = array(
		"title" => 'socialnutzer_row',
		"template"	=> '<table width="70%">
	<tr>
		<td  align="left" width="90px"><img src="{$avatar}" width="70px"/> </td>
		<td><a href="{$profilelink}">{$username}</a><br>
			{$userpagelink}  </td>
	</tr>
</table>',
		"sid"		=> "-1",
		"version"	=> "1.0",
		"dateline"	=> TIME_NOW
	);

	foreach ($template as $row) {
		$db->insert_query("templates", $row);
	}
	
	include  MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("member_profile", "#".preg_quote('<span class="largetext"><strong>{$formattedname}</strong></span><br />')."#i", '<span class="largetext"><strong>{$formattedname}</strong></span><br />'."\n".'{$userpagelink}');
}
/*
*	End Plugin Activate
*/

/*
*	Plugin De-Activate
*/
function userpages_deactivate() {
    global $db;
    
    $query = $db->simple_select("settinggroups", "gid", "name='userpages'");
    $gid = $db->fetch_field($query, 'gid');
    $db->delete_query("settinggroups", "gid='".$gid."'");
    $db->delete_query("settings", "gid='".$gid."'");
    $db->delete_query("templates", "title LIKE 'userpages_%'");
    $db->delete_query("templates", "title LIKE 'social_%'");
    $db->delete_query("templates", "title LIKE 'social_post'");
    rebuild_settings();
    
    include  MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("member_profile", "#".preg_quote("\n".'{$userpagelink}')."#i", '');
}
/*
*	End Plugin De-Activate
*/

/*
*	Plugin Uninstall
*/
function userpages_uninstall() {
    global $db, $cache;
    global $db;
	$db->drop_table("socialfriends");
	$db->drop_table("socialantwort");
	$db->drop_table("socialpost");
	$db->drop_table("sociallikes");
	
    
    $db->query("ALTER TABLE `".TABLE_PREFIX."users` DROP `userpage`, DROP `social_titelbild`, DROP `social_nutzername`, DROP `social_profilbild`, DROP `social_friendcheck`, DROP `social_postcheck`, DROP `social_likecheck`, DROP `social_namedcheck` ;");
    $db->query("ALTER TABLE `".TABLE_PREFIX."usergroups` DROP `canuserpage` , DROP `canuserpageedit` , DROP `canuserpagemod` ;");
    $cache->update_usergroups();
}
/*
*	End Plugin Uninstall
*/


/*
*	Usergroup permissions
*	This function writes the permission checkboxes out to the permissions page
*/
$plugins->add_hook("admin_formcontainer_end", "userpages_edit_group");
function userpages_edit_group()
{
	global $run_module, $form_container, $lang, $form, $mybb, $user;
	
	$lang->load("userpages");

	if($run_module == 'user' && !empty($form_container->_title) && !empty($lang->users_permissions) && $form_container->_title == $lang->users_permissions)
	{
		$userpages_options = array();
		$userpages_options[] = $form->generate_check_box('canuserpage', 1, $lang->userpages_perm_base, array('checked' => $mybb->input['canuserpage']));
		$userpages_options[] = $form->generate_check_box('canuserpageedit', 1, $lang->userpages_perm_edit, array('checked' => $mybb->input['canuserpageedit']));	
		$userpages_options[] = $form->generate_check_box('canuserpagemod', 1, $lang->userpages_perm_mod, array('checked' => $mybb->input['canuserpagemod']));

		$form_container->output_row($lang->userpages_perm, '', '<div class="group_settings_bit">'.implode('</div><div class="group_settings_bit">', $userpages_options).'</div>');
	}
}
/*
*	End Usergroup Permissions
*/

/*
*	Usergroup Permissions
*	This function retrieves the permissions sent from the previous function and saves the permission settings
*/
$plugins->add_hook("admin_user_groups_edit_commit", "userpages_edit_group_do");
function userpages_edit_group_do()
{
	global $updated_group, $mybb;

	$updated_group['canuserpage'] = intval($mybb->input['canuserpage']);
	$updated_group['canuserpageedit'] = intval($mybb->input['canuserpageedit']);
	$updated_group['canuserpagemod'] = intval($mybb->input['canuserpagemod']);
}
/*
*	End Usergroup Permissions
*/

/*
*	Cache templates for userpages
*/
$plugins->add_hook("global_start", "userpages_templatecache");
function userpages_templatecache() {
	global $templatelist;
	
	$templatelist .= ",userpages_content,userpages_modcp_main,userpages_modcp_modify,userpages_modcp_nav,userpages_modcp_singleuser,userpages_usercp_main,userpages_usercp_nav,smilieinsert,codebuttons,";
}
/*
*	End cached templates for userpages
*/

/*
*	UserCP Menu
*	This function creates a link to the userpage editor at the bottom of the UserCP menu
*/
$plugins->add_hook("usercp_menu", "userpages_usercpmenu", 40);
function userpages_usercpmenu() 
{
	global $db, $mybb, $templates, $theme, $usercpmenu, $lang, $collapsed, $collapsedimg, $lang, $cache;
	
	$usergroups_cache = $cache->read("usergroups");
	
	$lang->load("userpages");
	
	if ($usergroups_cache[$mybb->user['usergroup']]['canuserpage'] && $usergroups_cache[$mybb->user['usergroup']]['canuserpageedit']) {
		eval("\$usercpmenu .= \"".$templates->get("userpages_usercp_nav")."\";");
	}
}
/*
*	End UserCP Menu
*/

/*
*	UserCP
*	This function handles everything else related to the UserCP
*/
$plugins->add_hook("usercp_start", "userpages_usercp");
function userpages_usercp() 
{
	global $mybb, $db, $lang, $cache, $page, $templates, $theme, $headerinclude, $header, $footer, $usercpnav, $smilieinserter, $codebuttons, $currentuserpage, $userpagelink;

	$lang->load('userpages');

	$userpagelink = '<span class="smalltext"><a href="member.php?action=profile&uid='.$mybb->user['uid'].'&area=userpage">Userpage ansehen</a></span>';
	
	
	$usergroups_cache = $cache->read("usergroups");
	
	if ($mybb->input['action'] == "edituserpage") {
		add_breadcrumb($lang->nav_usercp, "usercp.php");
		add_breadcrumb($lang->changeuserpage, "usercp.php?action=edituserpage");
	
		if (!$usergroups_cache[$mybb->user['usergroup']]['canuserpage'] || !$usergroups_cache[$mybb->user['usergroup']]['canuserpageedit']) {
			error_no_permission();
		}
		
		//$smilieinserter = build_clickable_smilies();
		//$codebuttons = build_mycode_inserter("userpage_content");
		$nutzername = $db->fetch_field($db->simple_select("users","social_nutzername", "uid = ".$mybb->user['uid']), "social_nutzername");
		$titelbild = $db->fetch_field($db->simple_select("users","social_titelbild", "uid = ".$mybb->user['uid']), "social_titelbild");
		$profilbild = $db->fetch_field($db->simple_select("users","social_profilbild", "uid = ".$mybb->user['uid']), "social_profilbild");
		
		$currentuserpage = htmlspecialchars($db->fetch_field($db->simple_select("users", "userpage", "uid = ".$mybb->user['uid']), "userpage"));
		


		$pn_friend = $db->fetch_field($db->simple_select("users","social_friendcheck", "uid = ".$mybb->user['uid']), "social_friendcheck");
		$post_check = $db->fetch_field($db->simple_select("users","social_postcheck", "uid = ".$mybb->user['uid']), "social_postcheck");
		$likes_check = $db->fetch_field($db->simple_select("users","social_likecheck", "uid = ".$mybb->user['uid']), "social_likecheck");
		$named_check = $db->fetch_field($db->simple_select("users","social_namedcheck", "uid = ".$mybb->user['uid']), "social_namedcheck");

		$check_friend ="";
		if ($pn_friend =="1"){
			$check_friend = "checked=\"checked\"";
		} else {
			$check_friend ="";
		}
		$check_post ="";
		if ($post_check =="1"){
			$check_post = "checked=\"checked\"";
		} else {
			$check_post ="";
		}
		$check_likes ="";
		if ($likes_check =="1"){
			$check_likes = "checked=\"checked\"";
		} else {
			$check_likes ="";
		}
		$check_named ="";
		if ($named_check =="1"){
			$check_named = "checked=\"checked\"";
		} else {
			$check_named ="";
		}
		

		eval("\$page = \"".$templates->get('userpages_usercp_main')."\";");
		output_page($page);
		die();
	}
	elseif ($mybb->input['action'] == "edituserpage_do" && $mybb->request_method == "post") {
	
		if (!$usergroups_cache[$mybb->user['usergroup']]['canuserpage'] || !$usergroups_cache[$mybb->user['usergroup']]['canuserpageedit']) {
			error_no_permission();
		}
		
		verify_post_check($mybb->input['my_post_key']);
		
		//checkboxen Benachrichtigung. 
		if(isset($_POST['pn_friend'])) {
			$pn_friend ="1";
		} else {
			$pn_friend="0";
		}
		
		if(isset($_POST['pn_post'])) {
			$pn_post ="1";
		} else {
			$pn_post="0";
		}
		
		if(isset($_POST['pn_like'])) {
			$pn_like ="1";
		} else {
			$pn_like="0";
		}
		
		if(isset($_POST['pn_named'])) {
			$pn_named ="1";
		} else {
			$pn_named="0";
		}

		
		$updatequery = array(
			'userpage' => $db->escape_string(trim($mybb->input['userpage_content'])),
			'social_nutzername' => $db->escape_string($mybb->input['nutzername']),
			'social_profilbild' => $db->escape_string($mybb->input['profilbild']),
			'social_titelbild' => $db->escape_string($mybb->input['titelbild']),
			'social_friendcheck' => intval($pn_friend),
			'social_postcheck' => intval($pn_post),
			'social_likecheck' => intval($pn_like),
			'social_namedcheck' => intval($pn_named)
		);
		
		
		if ($db->update_query("users", $updatequery, "uid = ".$mybb->user['uid'])) {
			redirect("usercp.php?action=edituserpage", $lang->userpage_updated);
		}
		else {
			redirect("usercp.php?action=edituserpage", $lang->userpage_notpdated);
		}
	}	
}
/*
*	End UserCP
*/


/*
*	ModCP
*	This function manages everything related to Userpages in the ModCP
*/
$plugins->add_hook("modcp_start", "userpages_modcp");
function userpages_modcp() 
{
	global $mybb, $db, $cache, $lang, $templates, $theme, $headerinclude, $header, $footer, $modcp_nav, $altbg, $userpages_users, $multipage, $smilieinserter, $codebuttons;
	
	$lang->load("userpages");
	
	$usergroups_cache = $cache->read("usergroups");
	
	/*
	*	Adding a link to the ModCP Menu.
	*	This has to be done by replacing the current ModCP Menu as it's created before the hook
	*/
	
	eval("\$newentry = \"".$templates->get('userpages_modcp_nav')."\";");
	$modcp_nav = str_replace("</table>", $newentry."</table>", $modcp_nav);
	
	if ($mybb->input['action'] == "userpages") {
		if (!$usergroups_cache[$mybb->user['usergroup']]['canuserpagemod']) {
			error_no_permission();
		}
		
		add_breadcrumb($lang->nav_modcp, "modcp.php");
		add_breadcrumb($lang->userpages_modcp, "modcp.php?action=userpages");
		
		$page = intval($mybb->input['page']);
		
		if($page < 1) {
			$page = 1;
		}
		
		$query = $db->simple_select("users", "uid, username, usergroup", "userpage != ''", array( 'limit_start' => (($page-1)*10), 'limit' => 10));
	
		$altbg = "trow2";
		
		$viewuserpage = $lang->viewuserpage;
		while ($user = $db->fetch_array($query)) {
			if ($altbg == "trow1") {
				$altbg = "trow2";
			}
			else {
				$altbg = "trow1";
			}
			
			$user['edituserpagelink'] = $mybb->settings['bburl']."/modcp.php?action=userpages_edit&amp;uid=".$user['uid'];
			
			$lang->viewuserpage = $lang->sprintf($viewuserpage, $user['username']);
			$user['username'] = format_name(htmlspecialchars_uni($user['username']), $user['usergroup']);
			
			if ($mybb->settings['seourls'] == "yes" || ($mybb->settings['seourls'] == "auto" && $_SERVER['SEO_SUPPORT'] == 1)) {
				$sep = "?";
			}
			else {
				$sep = "&amp;";
			}
			
			$user['viewuserpagelink'] = get_profile_link($user['uid']).$sep."area=userpage";
			eval("\$userpages_users .= \"".$templates->get('userpages_modcp_singleuser')."\";");
		}
		
		$numusers = $db->fetch_field($db->simple_select("users", "COUNT(uid) AS count", "userpage != ''"), "count");
		$multipage = multipage($numusers, $mybb->settings['threadsperpage'], $page, $_SERVER['PHP_SELF']."?action=userpages");
		
		eval("\$page = \"".$templates->get('userpages_modcp_main')."\";");
		output_page($page);
		die();
	}
	elseif ($mybb->input['action'] == "userpages_edit") {
		if (!$usergroups_cache[$mybb->user['usergroup']]['canuserpagemod']) {
			error_no_permission();
		}
		
		$uid = intval($mybb->input['uid']);
		$query = $db->simple_select("users", "username, userpage", "uid = ".$uid);
	
		$pn_friend = $db->fetch_field($db->simple_select("users","social_friendcheck", "uid = '$uid'"), "social_friendcheck");
		$post_check = $db->fetch_field($db->simple_select("users","social_postcheck", "uid = '$uid'"), "social_postcheck");
		$likes_check = $db->fetch_field($db->simple_select("users","social_likecheck", "uid = '$uid'"), "social_likecheck");
		$named_check = $db->fetch_field($db->simple_select("users","social_namedcheck", "uid = '$uid'"), "social_namedcheck");
		$nutzername = $db->fetch_field($db->simple_select("users","social_nutzername", "uid = '$uid'"), "social_nutzername");
		$titelbild = $db->fetch_field($db->simple_select("users","social_titelbild", "uid = '$uid'"), "social_titelbild");
		$profilbild = $db->fetch_field($db->simple_select("users","social_profilbild", "uid =  '$uid'"), "social_profilbild");
		
		$content = $db->fetch_array($query);
		
		$check_friend ="";
		if ($pn_friend =="1"){
			$check_friend = "checked=\"checked\"";
		} else {
			$check_friend ="";
		}
		$check_post ="";
		if ($post_check =="1"){
			$check_post = "checked=\"checked\"";
		} else {
			$check_post ="";
		}
		$check_likes ="";
		if ($likes_check =="1"){
			$check_likes = "checked=\"checked\"";
		} else {
			$check_likes ="";
		}
		$check_named ="";
		if ($named_check =="1"){
			$check_named = "checked=\"checked\"";
		} else {
			$check_named ="";
		}
		
		$content['userpage'] = htmlspecialchars_uni($content['userpage']);
		
		//smiley box auskommentiert
		//$smilieinserter = build_clickable_smilies();
		//$codebuttons = build_mycode_inserter("userpage_content");
		
		$lang->userpages_modcp_modify = $lang->sprintf($lang->userpages_modcp_modify, $content['username']);
		
		
		add_breadcrumb($lang->nav_modcp, "modcp.php");
		add_breadcrumb($lang->userpages_modcp, "modcp.php?action=userpages");
		add_breadcrumb($lang->userpages_modcp_modify);
		
		eval("\$page = \"".$templates->get('userpages_modcp_modify')."\";");
		output_page($page);
		die();
	}
	elseif ($mybb->input['action'] == "userpages_edit_do" && $mybb->request_method == "post") {
		if (!$usergroups_cache[$mybb->user['usergroup']]['canuserpagemod']) {
			error_no_permission();
		}
		
		verify_post_check($mybb->input['my_post_key']);
		
		$updatequery = array(
			'userpage' => $db->escape_string(trim($mybb->input['userpage_content'])),
			'social_nutzername' => $db->escape_string($mybb->input['nutzername']),
			'social_profilbild' => $db->escape_string($mybb->input['profilbild']),
			'social_titelbild' =>  $db->escape_string($mybb->input['titelbild']),
			'social_friendcheck' => intval($pn_friend),
			'social_postcheck' => intval($pn_post),
			'social_likecheck' => intval($pn_like),
			'social_namedcheck' => intval($pn_named)
		);
		
		if ($db->update_query("users", $updatequery, "uid = ".$mybb->input['uid'])) {
			redirect("modcp.php?action=userpages", $lang->userpage_updated);
		}
		else {
			redirect("modcp.php?action=userpages", $lang->userpage_notpdated);
		}
	}
}
/*
*	End ModCP
*/



/*
*	User Profile
*	This is the main function that displays the actual Userpage
*/

$plugins->add_hook("member_profile_start", "userpages_main"); //,"index_start"
//$plugins->add_hook("global_start", "userpages_main"); //,"index_start"
	
function userpages_main() 
{
	global $mybb, $db, $memprofile, $lang, $cache, $userpage_parser, $templates, $theme, $headerinclude, $header, $footer, $page, $parser, $userpagelink, $commentid, $sociallink_logo, $gotoedit, $social_link, $headerlink_newsfeed, $hearstrikeslink;
	
	$lang->load('userpages');
	$usergroups_cache = $cache->read("usergroups");
	
	//von wem ist das Profil?
	$memprofile = $db->fetch_array($db->simple_select("users", "userpage, username, uid, social_nutzername", "uid = ".intval($mybb->input['uid'])), "userpage");
	
	//hat der user einen eigenen Nutzernamen gewählt? Sonst - Username
	if ($memprofile['social_nutzername'] !== "") {
		$nutzername = htmlspecialchars_uni($memprofile['social_nutzername']);
	} else {
		$nutzername = htmlspecialchars_uni($memprofile['username']);
	}
	
	//link um userpage zu bearbeiten, wenn eigene seite
	if ($memprofile['uid'] == $mybb->user['uid']) {
	 $gotoedit = '<span class="smalltext"><a href="usercp.php?action=edituserpage"> - Userpage bearbeiten</a></span>';
	}
	
	if ($memprofile['userpage'] !== "") {
		if ($mybb->settings['seourls'] == "yes" || ($mybb->settings['seourls'] == "auto" && $_SERVER['SEO_SUPPORT'] == 1)) {
			$sep = "?";
		}
		else {
			$sep = "&amp;";
		}
		
		//link zur userpage
		$userpagelink = '<span class="smalltext"><a href="member.php?action=profile&uid='.$memprofile['uid'].'&area=userpage">'.$lang->sprintf($lang->viewuserpage, $nutzername).'</a></span><br />';
		//link zur hs seite mit logo
		$sociallink_logo ='<a href="'.get_profile_link(intval($mybb->input['uid'])).$sep.'area=userpage"><img src="social/logo_80px.png" border="0" alt="logo" /></a>';
	
		// fürs lslv
		$hearstrikeslink ='<a href="'.get_profile_link(intval($mybb->input['uid'])).$sep.'area=userpage"><img src="social/logo_80px.png" border="0" alt="logo" /></a>';
	
	}
	
	//nochmal ^^?
	$social_link = '<a href="'.get_profile_link(intval($mybb->input['uid'])).$sep.'area=userpage">social network</a>';
	
	
	//wenn keine erlaubnis social network zu sehen
	if ($mybb->input['area'] == "userpage") {
		if (!$usergroups_cache[$mybb->user['usergroup']]['canuserpage']) {
			error_no_permission();
		}
		
		$lang->nav_profile = $lang->sprintf($lang->nav_profile, $memprofile['username']);
		$lang->viewinguserpage = $lang->sprintf($lang->viewinguserpage, $memprofile['username']);
		add_breadcrumb($lang->nav_profile, get_profile_link($memprofile['uid']));
		add_breadcrumb($lang->viewinguserpage);

		$options = array(
			"allow_html" => $mybb->settings['userpages_html_active'],
			"allow_mycode" => $mybb->settings['userpages_mycode_active'],
			"allow_smilies" => 1,
			"allow_imgcode" => $mybb->settings['userpages_images_active'],
			"filter_badwords" => $mybb->settings['userpages_badwords_active'],
			"nl2br" => 1,
			"allow_videocode" => $mybb->settings['userpages_videos_active'],
			"me_username" => $memprofile['username'],
		);
		
		$memprofile['userpage'] = $parser->parse_message($memprofile['userpage'], $options);		
		$memprofile['view_full_profile'] = '<a href="'.get_profile_link(intval($memprofile['uid'])).'" class="social_profillink">Zum Profil</a>';


// Ganz viel Kram fürs Netzwerk - Haupteil:

// Variable setzen um die richtigen Posts auf der richtigen Userpage anzuzeigen
$mpid = intval($memprofile['uid']); 
//variable um zu speichern welcher User gerade online ist
$thisuser = intval($mybb->user['uid']);
//Name vom nutzer der online ist, der beim social work genutzt werden soll, wenn ausgefüllt
$benutzername = htmlspecialchars_uni($mybb->user['social_nutzername']);

if($benutzername == ""){
	$benutzername = htmlspecialchars_uni($mybb->user['username']);
}
if($benutzername == "NULL"){
	$benutzername = htmlspecialchars_uni($mybb->user['username']);
}

//Freunde hinzufügen/löschen/ausgeben:

//als Freund hinzufuegen
if ($_POST['addfriend']){
	$friendname = htmlspecialchars_uni($memprofile['social_nutzername']);
	if($friendname == ""){
		$friendname= htmlspecialchars_uni($memprofile['username']);
	}
	if($friendname == "NULL"){
		$friendname= htmlspecialchars_uni($memprofile['username']);
	}
	
	$addfriend = array(
	//wer hat angefragt
	"uid" => $mybb->user['uid'],
	//bei
	"isfriend" => $memprofile['uid'],
	"username"=> $db->escape_string($friendname),
	"asked" => 0,
	//noch nicht akzepiert
	"accepted"=> 0);
	$db->insert_query("socialfriends", $addfriend); 
		

	$addfriendback = array(
	//mit wem wurde gefragt
	"isfriend" => $mybb->user['uid'],
	//wer wurde gefragt
	"uid" => $memprofile['uid'],
	"username" => $db->escape_string($benutzername),
	"asked" => 1,
	"accepted"=> 0); 
	$db->insert_query("socialfriends", $addfriendback); 
	

	$pn_friend = $db->fetch_field($db->simple_select("users","social_friendcheck", "uid = '$mpid'"), "social_friendcheck");
	if($pn_friend == "1"){	
		$pm = array(
			'subject' => 'social network - Freundschaftsanfrage',
			'message' => $benutzername." möchte dein Freund sein. Gehe auf deine <a href=\"member.php?action=profile&uid=".$mpid."&area=userpage\">Userpage</a> um die Anfrage zu akzeptieren.",
			'touid' => intval($memprofile['uid']),
			'language' => $user['language'],
			'language_file' => 'usercp'
		);
		send_pm($pm, -1, true);
	}
	redirect("member.php?action=profile&uid=".intval($memprofile['uid'])."&area=userpage", "Userpage");
}

//Freunde löschen
if ($_POST['delfriend']){
	$user=intval($mybb->user['uid']);
  	$db->query("DELETE FROM ".TABLE_PREFIX."socialfriends WHERE (isfriend = '$mpid' OR uid ='$mpid') AND isfriend = '$user'");
  	redirect("member.php?action=profile&uid=".$mpid."&area=userpage", "Userpage");
}

//Freunde akzeptieren
if ($_POST['accept']){
	$add = intval($_POST['wannabe']);
	$db->query("UPDATE ".TABLE_PREFIX."socialfriends SET accepted = '1' WHERE (isfriend = '$add' AND uid = '$mpid' ) OR (uid = '$add' AND isfriend ='$mpid')");
	$pn_friend = $db->fetch_field($db->simple_select("users","social_friendcheck", "uid = '$add'"), "social_friendcheck");
	if($pn_friend == "1"){	
	$pm = array(
		'subject' => 'social network - Freundschaftsanfrage',
		'message' => $benutzername." hat deine Freundschaftsanfrage akzeptiert.",
		'touid' => intval($mybb->input['wannabe']),
		'language' => $user['language'],
		'language_file' => 'usercp'
	);
	send_pm($pm, -1, true);
	}
	redirect("member.php?action=profile&uid=".$mpid."&area=userpage", "Userpage");
}

//Freunde ablehnen
if ($_POST['deny']){
	$add = intval($_POST['wannabe']);
	$db->query("DELETE FROM ".TABLE_PREFIX."socialfriends WHERE (uid = '$mpid' AND isfriend='$add') OR (uid = '$add' AND isfriend='$mpid')");
	
	$pn_friend = $db->fetch_field($db->simple_select("users","social_friendcheck", "uid = '$add'"), "social_friendcheck");
	//nur wenn Benachrichtigung erlaubt
	if($pn_friend == "1"){	
		$pm = array(
			'subject' => 'social network - Freundschaftsanfrage',
			'message' => $benutzername." hat deine Freundschaftsanfrage abgelehnt.",
			'touid' => intval($mybb->input['wannabe']),
			'language' => $user['language'],
			'language_file' => 'usercp'
		);
		send_pm($pm, -1, true);
	}
	redirect("member.php?action=profile&uid=".$mpid."&area=userpage", "Userpage");

}


//lösche den Freund, auf dessen userpage du bist
if ($_POST['delfriend']){
$user=intval($mybb->user['uid']);

$db->query("DELETE FROM ".TABLE_PREFIX."socialfriends WHERE (uid='$user' AND isfriend = '$mpid') OR (uid='$mpid' AND isfriend='$user')");

redirect("member.php?action=profile&uid=".$mpid."&area=userpage", "Userpage");

}


 //lösche einen Freund von deiner eigenen userpage aus
if ($_POST['delfromownpage']){
	$user=$mybb->user['uid'];
	$delfriend= $_POST['get_friendid'];
	//Wir sind auf Seite 70 und sind $user -> 70 und klicken auf löschen where  delfriend
	$db->query("DELETE FROM ".TABLE_PREFIX."socialfriends WHERE (uid = '$mpid' AND isfriend='$delfriend') OR (uid = '$delfriend' and isfriend='$mpid')");
	redirect("member.php?action=profile&uid=".$mpid."&area=userpage", "Userpage");
} 

if(($thisuser == $mpid)) {
	eval("\$social_delfriendownpage .=\"".$templates->get("social_delfriendownpage")."\";");
}

 //Freundschaftsanfrage zurücknehmen
if ($_POST['delanfrage']){
	$user=intval($mybb->user['uid']);
	$delfriend= intval($_POST['get_friendid']);

	$db->query("DELETE FROM ".TABLE_PREFIX."socialfriends WHERE (uid = '$mpid' AND isfriend='$delfriend') OR (uid = '$delfriend' and isfriend='$mpid')");
	
	$pn_friend = $db->fetch_field($db->simple_select("users","social_friendcheck", "uid = '$delfriend'"), "social_friendcheck");
	//nur wenn Benachrichtigung erlaubt
	if($pn_friend == "1"){	
		$pm = array(
			'subject' => 'social network - Freundschaftsanfrage',
			'message' => $benutzername." hat seine Freundschaftsanfrage zurückgenommen.",
			'touid' =>  intval($mybb->input['get_friendid']),
			'language' => $user['language'],
			'language_file' => 'usercp'
		);
		send_pm($pm, -1, true);
	}
	redirect("member.php?action=profile&uid=".$mpid."&area=userpage", "Userpage");
} 

if(($thisuser == $mpid)) {
	eval("\$social_delanfrage .=\"".$templates->get("social_delanfrage")."\";");
}


$get_fids=$db->query("SELECT uid,username,social_profilbild,social_titelbild,social_nutzername FROM ".TABLE_PREFIX."users WHERE uid='$mpid'");
while($profilfeld=$db->fetch_array($get_fids)) {
	$profilbild = htmlspecialchars_uni($profilfeld['social_profilbild']);
	$titelbild = htmlspecialchars_uni($profilfeld['social_titelbild']);
	$nutzername = htmlspecialchars_uni($profilfeld['social_nutzername']);
	if ($nutzername =="") {
		$nutzername = htmlspecialchars_uni($profilfeld['username']);
	}
}

//Freundliste kreieren
//akzeptierte Freunde
$get_friends=$db->query("SELECT * FROM 
		(SELECT * FROM ".TABLE_PREFIX."socialfriends WHERE uid = '$mpid') AS friends
			JOIN 
		(SELECT uid, social_profilbild, social_nutzername, username u_username FROM ".TABLE_PREFIX."users) AS fields
		ON fields.uid = isfriend WHERE accepted = '1' ORDER by username");

while($friends=$db->fetch_array($get_friends)){
	$friendid = $friends['isfriend'];
	//aus tabelle friends
	$friendname = htmlspecialchars_uni($friends['social_nutzername']);
	if ($friendname =="") {
		$friendname = htmlspecialchars_uni($friends['u_username']);
	}
	$friendava = htmlspecialchars_uni($friends['social_profilbild']);
		
	if($friendava=="") {
		//ANPASSEN: Adresse zum alternativen Profilbild wenn leer
		$friendava = "social/profil_leer.png";
	}
	if($friendid != "") {
		$social_friends_title="<tr><td colspan=\"2\"><b>Freunde</b></td></tr>";
	} else {
		$social_friends_title ="";
	}
	//Template für die Ausgabe der freunde
	eval("\$social_friends .=\"".$templates->get("social_friends")."\";");
} 

$userisfriend="false";

$get_friendsForButton=$db->query("SELECT * FROM ".TABLE_PREFIX."socialfriends 
							WHERE isfriend = '$thisuser'
							AND uid = '$mpid'");	
							

$result = mysqli_num_rows($get_friendsForButton);								

//Variablen auf null setzen
$social_delfriends="";
$social_addfriends="";

	if($result > 0) {
	eval("\$social_delfriends = \"".$templates->get("social_delfriends")."\";");
	} 
	else {
	eval("\$social_addfriends = \"".$templates->get("social_addfriends")."\";");		
	}

$schon_gefragt=$db->query("SELECT uid FROM ".TABLE_PREFIX."socialfriends 
							WHERE (isfriend = '$thisuser')
							AND (uid = '$mpid') 
							AND (asked = '1')
							AND (accepted = '0')");	
							
$result_schongefragt = mysqli_num_rows($schon_gefragt);
if ($result_schongefragt > 0){
$social_delfriends = "Freundschaftsanfrage gesendet. <br />";
}


$hat_dich_gefragt=$db->query("SELECT uid FROM ".TABLE_PREFIX."socialfriends 
							WHERE isfriend = '$mpid'
							AND uid = '$thisuser' 
							AND asked = '1'
							AND accepted = '0'");
$result_hat_dich_gefragt = mysqli_num_rows($hat_dich_gefragt);
if ($result_hat_dich_gefragt > 0){
	$social_delfriends = "Du wurdest schon angefragt. <br />";
}


if($thisuser == $mpid) {
	$social_delfriends="";
	$social_addfriends="";
}	


//zu akzeptierende Freunde
$get_wannafriends=$db->query("
	SELECT * FROM 
		(SELECT * FROM ".TABLE_PREFIX."socialfriends WHERE uid = '$mpid') AS friends
	LEFT JOIN 
		(SELECT uid,social_profilbild,social_nutzername,username u_username FROM ".TABLE_PREFIX."users) AS fields
	ON fields.uid = isfriend WHERE accepted = '0' AND asked='1' ORDER by username");

while($friends=$db->fetch_array($get_wannafriends)){
	$friendid = $friends['isfriend'];
	$friendname = htmlspecialchars_uni($friends['social_nutzername']);
	if ($friendname =="") {
		$friendname = htmlspecialchars_uni($friends['u_username']);
	}
	$friendava = htmlspecialchars_uni($friends['social_profilbild']);
	$asked = $friends['asked'];
	
	if($friendid != "" && ($thisuser == $mpid)) {
		$social_friendtoadd_title="<tr><td colspan=\"2\"><b>Freundschaftsanfragen</b></td></tr>";
	} else {
		$social_friendtoadd_title ="";
	}
		
	if($friendava=="") {
		$friendava = "social/profil_leer.png";
	}
	
	if(($thisuser == $mpid)) {
	//Template für die Ausgabe der Antworten
		eval("\$social_wannafriends .=\"".$templates->get("social_wannafriends")."\";");
	}
}  

//angefragten freunde
$get_friendsiasked=$db->query("
	SELECT * FROM 
		(SELECT * FROM ".TABLE_PREFIX."socialfriends WHERE uid = '$mpid') AS friends
	LEFT JOIN 
		(SELECT uid,social_profilbild,social_nutzername, username u_username FROM ".TABLE_PREFIX."users) AS fields
	ON fields.uid = isfriend WHERE accepted = '0' AND asked='0' ORDER by username");
		
while($n_friends=$db->fetch_array($get_friendsiasked)){
	$friendid = $n_friends['isfriend'];
	$friendname = htmlspecialchars_uni($n_friends['social_nutzername']);
	if ($friendname == "") {
		$friendname = htmlspecialchars_uni($n_friends['u_username']);
	}
		if ($friendname == "NULL") {
		$friendname = htmlspecialchars_uni($n_friends['u_username']);
	}
	$asked = $n_friends['asked'];
	$uid = $n_friends['uid'];
		
	if($friendid != "" && ($thisuser == $mpid)) {
		$social_friendsiaksed_title="<tr><td colspan=\"2\"><b>gesendete Freundschaftsanfragen</b></td></tr>";
	} else {
		$social_friendsiaksed_title ="";
	}
	
	//Template für die Freunde die man angefragt hat
	if($thisuser == $mpid) {
		eval("\$social_friendsiasked .=\"".$templates->get("social_friendsiasked")."\";");
	}
}  


//Gefällt mir Funktion
//Post liken
if ($_POST['like'] == 'like'){
	$id= $mybb->user['uid']; 
	$name = $db->escape_string($benutzername);
	$userpagelink ="<a href=\"member.php?action=profile&uid=".$id."&area=userpage\">".$name."</a>";
	$postautor = $_POST['postautorid'];
	$postid =$_POST['postid'];
	
	$likes = array(
		"user" => $userpagelink,
		"postid"=> intval($_POST['postid']),
		"uid"=> intval($mybb->user['uid'])
	);

	$db->insert_query("sociallikes", $likes); 
	$pn_likes = $db->fetch_field($db->simple_select("users","social_likecheck", "uid = '$postautor'"), "social_likecheck");
	//nur wenn Benachrichtigung erlaubt
	if($pn_likes == "1"){			
		$pm = array(
			'subject' => 'social network Like',
			'message' => $benutzername." gefällt dein <a href=\"member.php?action=profile&uid=".$mpid."&area=userpage#".$postid."\">Post</a>.",
			'touid' =>intval($postautor),
			'language' => $user['language'],
			'language_file' => 'usercp'
		);
		send_pm($pm, -1, true);
	}	
	redirect("member.php?action=profile&uid=".$mpid."&area=userpage", "Userpage");
}

//like zurücknehmen
if ($_POST['like'] == 'dislike'){
	$id = intval($_POST['postid']); 
	$db->query("DELETE FROM ".TABLE_PREFIX."sociallikes WHERE uid = '$thisuser' && postid = '$id'");

	redirect("member.php?action=profile&uid=".$mpid."&area=userpage", "Userpage");
} 

//Gefällt mir Funktion
//Antwort liken
if ($_POST['likeantwort'] == 'like'){
	$id= intval($mybb->user['uid']); 
	$name = $db->escape_string($benutzername);
	$userpagelink ="<a href=\"member.php?action=profile&uid=".$id."&area=userpage\">".$name."</a>";
	$antwortid = intval($_POST['l_aid']);
	$postautor= intval($_POST['antautorname']);
	$likes = array(
		"user" => $userpagelink,
		"antwortid"=> intval($mybb->input['l_aid']),
		"uid"=> intval($mybb->user['uid'])
	);
	$db->insert_query("sociallikes", $likes); 
	
	//PM für Gefällt mir	
	$pn_likes = $db->fetch_field($db->simple_select("users","social_likecheck", "uid = '$postautor'"), "social_likecheck");
	//nur wenn Benachrichtigung erlaubt
	if($pn_likes == "1"){		
	$pm = array(
		'subject' => 'social network Like',
		'message' => $benutzername." gefällt deine <a href=\"member.php?action=profile&uid=".$mpid."&area=userpage#a".$antwortid."\">Antwort</a>.",
		'touid' => $postautor,
		'language' => $user['language'],
		'language_file' => 'usercp'
	);
	send_pm($pm, -1, true);
}
	redirect("member.php?action=profile&uid=".$mpid."&area=userpage", "Userpage");
}

//like zurücknehmen
if ($_POST['likeantwort'] == 'dislike'){
	$id = intval($_POST['l_aid']); 
	$db->query("DELETE FROM ".TABLE_PREFIX."sociallikes WHERE uid = '$thisuser' && antwortid = '$id'");
	redirect("member.php?action=profile&uid=".$mpid."&area=userpage", "Userpage");
} 


//Alles rund um die Beiträge
if ($_POST['senden'] != '' && $_POST['socialpost'] != '' && $mybb->user['uid'] != 0) {
	//datum setzen
 	$datum = sprintf("%d-%d-%d %d:%d:%d", $_POST['socialpost_year'], $_POST['socialpost_month'], $_POST['socialpost_day'], $_POST['std'], $_POST['min'], $_POST['sek']);
	//link zur userpage
	$userpagelink ="<a href=\"member.php?action=profile&uid=".$mybb->user['uid']."&area=userpage\"> ".$benutzername."</a>";
	
	$socialpost = array(
		"uid" => intval($mybb->user['uid']),
		"social_date" => $datum,
		"userpageid" => intval($memprofile['uid']), 
		"social_post" => $db->escape_string($mybb->input['socialpost']),
		"del_username"=> $db->escape_string($mybb->user['username']),
		"del_nutzername" => $db->escape_string($mybb->user['social_nutzername'])
		);	
	//speichern
	$db->insert_query("socialpost", $socialpost);
	
	//Benachrichtigung für User wird erwähnt:
	//inhalt speichern
	$post = $mybb->input['socialpost'];	
	
	$postid_query=$db->query("SELECT max(social_id) AS id FROM ".TABLE_PREFIX."socialpost");
	while($pid = $db->fetch_array($postid_query)) {
	$postid = intval($pid['id']);
	}

	//Benachrichtigungs PN an User wenn jemand auf dessen Seite einen Post gesetzt hat.
	$pn_post = $db->fetch_field($db->simple_select("users","social_postcheck", "uid = '$mpid'"), "social_postcheck");
	//nur wenn Benachrichtigung erlaubt
	if($pn_post == "1"){		
	$pm_post = array(
		'subject' => 'social network Post',
		'message' => $benutzername." hat einen <a href=\"member.php?action=profile&uid=".$mpid."&area=userpage#".$postid."\">Post</a> auf deiner Userpage geschrieben.",
		'touid' => $mpid,
		'language' => $user['language'],
		'language_file' => 'usercp'
		);
	//Nur PN schicken, wenn der User nicht auf seine eigene Page geschrieben hat.
	if ($thisuser != $mpid){
		send_pm($pm_post, -1, true); 
	}
	}
	

	// Alle User des Forums in ein Array speichern, Index d. Arrays = uid
	//arrays initalisieren
	$arruser_post = array();
	$arrmention_post = array();
	$arrmention_postnutz = array();
	$zaehler_post = 0;
	$zaehler_postnutz = 0;
	// diese in Array speichern
	$nutzername_array_post = array();
	
	//array für mentioned: Alle User aus der Datenbank - mit userername und uid gespeichert
	$alert_query_post=$db->query("SELECT username, uid FROM ".TABLE_PREFIX."users");
	
	while($alert_post = $db->fetch_array($alert_query_post)) {
		$username = htmlspecialchars_uni($alert_post['username']);
		$uid = intval($alert_post['uid']);
		$arruser_post[$uid] = $username;
	}

	//Array mit allen usernamen  durchgehen und schauen ob der Nutzer im Post erwähnt wurde
	foreach ($arruser_post as $postuid => $mentioned) {
		$createsearchstring = "@".$mentioned;
		$idnutzpost = $postuid;
		$tofind = "/$createsearchstring/";
		//echo "uid: $uid hat den Wert: $mentioned<br />";
		if( preg_match($tofind, $post) ) {
			$arrmention_post[zaehler_post] = $postuid;
			$zaehler_post++;
		}
	}
	// erstelltes array durchgehen und PMs verschicken
	foreach ($arrmention_post as $ment_uidnutz) {
		$pn_named = $db->fetch_field($db->simple_select("users","social_namedcheck", "uid = '$ment_uidnutz'"), "social_namedcheck");
		//nur wenn Benachrichtigung erlaubt
			if($pn_named == "1"){	
				$pm_postment = array(
				'subject' => 'Mentioned',
				'message' => $benutzername." hat dich in diesem <a href=\"member.php?action=profile&uid=".$mpid."&area=userpage#".$postid."\">Post</a> erwähnt.",
				'touid' => $ment_uidnutz,
				'language' => $user['language'],
				'language_file' => 'usercp'
				);
				send_pm($pm_postment, -1, true);
			} 
	}
	unset($arrmention_post);
	
	//array für mentioned: Alle Nutzer die einen eigenen nutzernamen haben
	$alert_nutzername=$db->query("SELECT social_nutzername, uid FROM ".TABLE_PREFIX."users WHERE (social_nutzername != '') AND (social_nutzername != username)");
	//müsste stimmen
	while($nutzername_alertquery = $db->fetch_array($alert_nutzername)) {
		$nutz_alert_name = $nutzername_alertquery['social_nutzername'];
		$nutz_alert_uid = $nutzername_alertquery['uid'];
		$nutzername_array_post[$nutz_alert_uid] = $nutz_alert_name;
	}
	
	// wird der selbst gewählte Nutzername erwähnt? 
	foreach ($nutzername_array_post as $nutz_alert_uid => $nutz_mentioned) {
		$nutz_createsearchstring = "@".$nutz_mentioned;
		$nutz_tofind = "/$nutz_createsearchstring/";
		//echo "uid: $uid hat den Wert: $mentioned<br />";
		if( preg_match($nutz_tofind, $post) ) {
			$arrmention_postnutz[zaehler_postnutz] = $nutz_alert_uid;
			$zaehler_postnutz++;	
		}
	}
	
	//mit gematchten userids durchgehen und pns schicken
	foreach($arrmention_postnutz as $ment_usernutz) {
		$pn_named = $db->fetch_field($db->simple_select("users","social_namedcheck","uid = '$ment_usernutz'"), "social_namedcheck");
		if($pn_named == "1") {
			//nur wenn Benachrichtigung erlaubt	
				$pm_postnutz = array(
				'subject' => 'Mentioned',
				'message' => $benutzername." hat dich in diesem <a href=\"member.php?action=profile&uid=".$mpid."&area=userpage#".$postid."\">Post</a> erwähnt.",
				'touid' => intval($ment_usernutz),
				'language' => $user['language'],
				'language_file' => 'usercp'
				);
				send_pm($pm_postnutz, -1, true);
		}
	} 
	unset($arrmention_postnutz);
	redirect("member.php?action=profile&uid=".$mpid."&area=userpage", "Userpage");
}
  	
//Antwort zum Post
if ($_POST['ant_senden'] != '' && $_POST['antwort'] != '' && $mybb->user['uid'] != 0){
		//Array erstellen um Daten von Antwort in die Datenbank zu speichern
		$ant_datum = sprintf("%d-%d-%d %d:%d:%d", $_POST['ant_year'], $_POST['ant_month'], $_POST['ant_day'], $_POST['ant_std'], $_POST['ant_min'], $_POST['ant_sek']);
		$userpagelink ="<a href=\"member.php?action=profile&uid=".$mybb->user['uid']."&area=userpage\">".$benutzername."</a>";
		
		$antworten = array(
			"social_date" =>$ant_datum,
			"userpageid" =>intval($memprofile['uid']),
			"social_uid" => intval($mybb->user['uid']),
			"antwort" => $db->escape_string($mybb->input['antwort']),
			"social_id"=>intval($mybb->input['get_socialid']),
			"del_username"=> $db->escape_string($mybb->user['username']),
			"del_nutzername" =>  $db->escape_string($mybb->user['social_nutzername'])
			); 
		//speichern
		$db->insert_query("socialantwort", $antworten);
	
	$postid_test=intval($mybb->input['postid']);
	$postid = intval($mybb->input['get_socialid']);
	//Query, um den Autor des Posts zu bekommen
	$get_uid=$db->query("SELECT * FROM ".TABLE_PREFIX."socialpost WHERE social_id = '$postid'	");
	while($uid=$db->fetch_array($get_uid)) {	
		$autor= intval($uid['uid']);
	}
	
	$postid_query=$db->query("SELECT max(social_aid) AS id FROM ".TABLE_PREFIX."socialantwort");
	while($pid = $db->fetch_array($postid_query)) {
	$postid = intval($pid['id']);
	}
	
	
	//PM an User von Userpage, dass jemand auf die Page geschrieben hat.
	$pn_post = $db->fetch_field($db->simple_select("users","social_postcheck", "uid = '$mpid'"), "social_postcheck");
	//nur wenn Benachrichtigung erlaubt
	if($pn_post == "1"){		
	$pm_antwort = array(
		'subject' => 'social network - Antwort',
		'message' => $benutzername." hat eine <a href=\"member.php?action=profile&uid=".$mpid."&area=userpage#a".$postid."\">Antwort</a> auf deiner Userpage geschrieben.",
		'touid' => intval($mpid),
		'language' => $user['language'],
		'language_file' => 'usercp'
	);
	//Keine PN wenn er auf seiner eigenen Seite geschrieben hat. 
	if ($thisuser != $mpid){
		send_pm($pm_antwort, -1, true); 	
	}
	}
	
	//PN an User, auf dessen Post geantwortet wurde. Will der Autor PNs?
	$pn_post2 = $db->fetch_field($db->simple_select("users","social_postcheck", "uid = '$autor'"), "social_postcheck");
	//nur wenn Benachrichtigung erlaubt
	if($pn_post2 == "1"){		
		$pm_antwort2 = array(
			'subject' => 'social network - Antwort auf einen Post',
			'message' => htmlspecialchars_uni($mybb->user['username'])." hat eine <a href=\"member.php?action=profile&uid=".$mpid."&area=userpage#a".$postid_test."\">Antwort</a> auf deinen Post geschrieben.",
			'touid' => $autor,
			'language' => $user['language'],
			'language_file' => 'usercp'
		);	
		if ($thisuser != $autor) {
			send_pm($pm_antwort2, -1, true);
		}
	}
	
	//Benachrichtigung für User wird erwähnt:
	$antwort = $mybb->input['antwort'];	

	
	// variablen initialisieren & leeren
	//alle user ids
	$arruser_ant = array();
	//alle user ids mit nutzermanen
	$nutzername_array_ant = array();
	
	//array mit erwähnten usernamen
	$arrmention_ant =array();
	//array mit erwähnten nutzernamen
	$nutzername_array_mentant=array();
	//zähler für arrmention_ant
	$zaehler = 0;
	//zähler für arrmention_mentant
	$zaehler_nutz = 0;
	
	$alert_query=$db->query("SELECT username,uid FROM ".TABLE_PREFIX."users");	
	// Alle User des Forums in ein Array speichern, Index des Arrays ist uid
	while($alert = $db->fetch_array($alert_query)) {
		$username = htmlspecialchars_uni($alert['username']);
		$ant_uid = intval($alert['uid']);
		$arruser_ant[$ant_uid] = $username;
	}
	
	//array mit allen usernamen durchgehen und schauen ob sie in der antwort erwähnt werden
	foreach($arruser_ant as $ant_uid => $mentioned) {
		$createsearchstring = "@".$mentioned;
		$tofind = "/$createsearchstring/";
		if (preg_match($tofind, $antwort)) {
			$arrmention_ant[zaehler] = $ant_uid;
			$zaehler++;
		}
	}
	
	//armention_ant durchgehen und pns verschicken
	foreach($arrmention_ant as $armention_ant_id) {
		$pn_named = $db->fetch_field($db->simple_select("users","social_namedcheck", "uid = '$armention_ant_id'"), "social_namedcheck");
		if($pn_named == "1"){	
				$pm_antwortment = array(
				'subject' => 'Mentioned',
				'message' => $benutzername." hat dich in diesem <a href=\"member.php?action=profile&uid=".$mpid."&area=userpage#a".$postid."\">Post</a> erwähnt.",
				'touid' => $armention_ant_id,
				'language' => $user['language'],
				'language_file' => 'usercp'
				);
				send_pm($pm_antwortment, -1, true);
			} 
	}
	unset($armention_ant);
	
	//array für mentioned: Alle Nutzer die einen eigenen nutzernamen haben
	$alert_nutzername_ant=$db->query("SELECT social_nutzername, uid FROM ".TABLE_PREFIX."users WHERE (social_nutzername != '') AND (social_nutzername != username)");


	while($nutzername_alertquery_ant = mysqli_fetch_array($alert_nutzername_ant)) {
		
		$nutz_alert_name_ant = htmlspecialchars_uni($nutzername_alertquery_ant['social_nutzername']);
		$nutz_alert_uid_ant = intval($nutzername_alertquery_ant['uid']);
		$nutzername_array_ant[$nutz_alert_uid_ant] = $nutz_alert_name_ant;
		//print_r($nutzername_array_ant);
	}
	
	foreach($nutzername_array_ant as $ant_uid_nutz => $mentioned) {
		$createsearchstring_nutz = "@".$mentioned;
		$tofind_nutz = "/$createsearchstring_nutz/";		
		if(preg_match($tofind_nutz, $antwort)) {
			$nutzername_array_mentant[zaehler_nutz] = $ant_uid_nutz;
			$zaehler_nutz++;
			
		}
		}
	
	foreach($nutzername_array_mentant as $nutzername_ant_id) {
		$pn_named = $db->fetch_field($db->simple_select("users","social_namedcheck", "uid = '$nutzername_ant_id'"), "social_namedcheck");
		if($pn_named == "1"){	
				$pm_nutzantment = array(
				'subject' => 'Mentioned',
				'message' => $benutzername." hat dich in diesem <a href=\"member.php?action=profile&uid=".$mpid."&area=userpage#a".$postid."\">Post</a> erwähnt.",
				'touid' => $nutzername_ant_id,
				'language' => $user['language'],
				'language_file' => 'usercp'
				);
				send_pm($pm_nutzantment, -1, true);
			} 
		//	print_r($nutzername_array_mentant);
	}
	unset($nutzername_array_mentant);
	
	redirect("member.php?action=profile&uid=".$mpid."&area=userpage", "Userpage");
}
  	
  	
//Post löschen
if (isset($_POST['loeschen'])){
   	$postautorid = intval($_POST['postautorid']);
	$postid = intval($_POST['postid']);
	//löschen wenn mod cp genutzt werden kann - oder autor
	if($mybb->usergroup['canmodcp'] == 1 || $mybb->user['uid'] == '$postautorid' ) {
		$db->query("DELETE FROM ".TABLE_PREFIX."socialpost WHERE social_id = '$postid'");
	} else {
		echo "<script>alert('Du darfst diesen Post nicht löschen');</script>";
	}
		redirect("member.php?action=profile&uid=".$mpid."&area=userpage", "Userpage");
}
	
//Antwort löschen
if (isset($_POST['aloeschen'])){
	$antautor = $_POST['antautorname'];
  	$l_aid = $_POST['l_aid'];
  	//Admin, Moderator und Autor dürfen löschen
  	if($mybb->usergroup['canmodcp'] == 1 || $mybb->user['uid'] == '$antautor') {
		$db->query("DELETE FROM ".TABLE_PREFIX."socialantwort WHERE social_aid = '$l_aid'");
	} else {
		echo "<script>alert('Du darfst diese Antwort nicht löschen');</script>";
	}
		redirect("member.php?action=profile&uid=".$mpid."&area=userpage", "Userpage");
}	

//Darstellung 

//query für UserpagePost
//thx @winterkind für die Erweiterung - Auch Posts in denen der User erwähnt wird, werden auf der Userpage angezeigt.
//Speicher username von aktueller userpage
$getname = $db->query("SELECT username FROM ".TABLE_PREFIX."users WHERE uid = '".$mpid."' LIMIT 1");

//array mit allen daten zum user
$nameget = $db->fetch_array($getname); 
//bekomme username
$nameget_name = $db->escape_string($nameget['username']);

//nutzername wieder leeren
$nutzername_get ="";

//eigener benutzername? 
$get_nutzername = $db->query("SELECT social_nutzername FROM ".TABLE_PREFIX."users WHERE uid = '".$mpid."' AND social_nutzername != '' And social_nutzername IS NOT NULL LIMIT 1 ");
//alle daten zum user
$query_nutzername = $db->fetch_array($get_nutzername); 
//nur der social username
$nutzername_get = $query_nutzername['social_nutzername'];

//gibt es einen eigenen Nutzernamen? Dann auch schauen, ob der irgendwo erwähnt wird
if (!empty($nutzername_get)){
	$nutzzeug = $query_nutzername['social_nutzername'];
	//escapen! -> wichtig für Namen mit sonderzeichen
	$nutzzeug = $db->escape_string($nutzzeug);
	$if_nutzername =" OR (social_post LIKE '%@".$nutzzeug."%')";
} 
$get_thisusername= $db->escape_string($nameget['username']);
//  OR (social_post LIKE '%@$nameget[username]%') OR (social_post LIKE '%@$nutzernameget[social_nutzername]%') -> wird der username oder nutzername im Post erwähnt? 
$social_postquery=$db->query("
	(
		SELECT social_id, getPost.uid, social_date, userpageid, social_post, social_profilbild, social_nutzername, fields.username as u_username FROM
			(SELECT * FROM ".TABLE_PREFIX."socialpost u WHERE (u.userpageid = '".$mpid."') OR (social_post LIKE '%@".$get_thisusername."%')".$if_nutzername.") AS getPost
		,
			(SELECT * FROM ".TABLE_PREFIX."users) as fields
	WHERE getPost.uid = fields.uid ORDER BY social_date DESC)
	UNION 
	(SELECT social_id, uid, social_date, userpageid, social_post, del_profilbild as social_profilbild, del_nutzername as social_nutzername, del_username as u_username FROM ".TABLE_PREFIX."socialpost sp 
	WHERE sp.userpageid = '".$mpid."' AND sp.uid NOT IN
(SELECT uid FROM ".TABLE_PREFIX."users) ) ORDER BY social_date DESC
");

	
//Daten vom Post holen
while($get_socialposts=$db->fetch_array($social_postquery)) {	
		//Variablen fürs tpl der Antwort leeren
		$social_antwort="";
		
		$thispageid=intval($get_socialposts['userpageid']);	
		$postid = intval($get_socialposts['social_id']);
		$postautorid = intval($get_socialposts['uid']);
		
		$username_post = htmlspecialchars_uni($get_socialposts['social_nutzername']);
		
		if ($username_post==""){
		$username_post=htmlspecialchars_uni($get_socialposts['u_username']);
		}		
		//link bauen wenn es den user noch gibt.
		$username = '<a href=member.php?action=profile&uid='.$postautorid.'&area=userpage>'.$username_post.'</a>';
		
		//gibt es den user noch? 
		$deleteduser_post=$db->query("SELECT uid FROM ".TABLE_PREFIX."users WHERE uid = $postautorid");
		$isdeleted_post = mysqli_num_rows($deleteduser_post);
		//wenn er gelöscht ist, dann kein link zur userpage
		if ($isdeleted_post == '0'){
			$username ="$username_post";
		}
		
		$avatar = htmlspecialchars_uni($get_socialposts['social_profilbild']);
		$for_pdatum = htmlspecialchars_uni($get_socialposts['social_date']);
		$post = $parser->parse_message($get_socialposts['social_post'], $options);
		$social_datum = date("d.m.Y H:i:s",  strtotime($for_pdatum));
		
	if($avatar=="") {
		$avatar = "social/profil_leer.png";
	}

	
	//query um die Antworten zu bekommen - nur die zu dem richtigen Posts auswählen		
	$antwort=$db->query("(SELECT social_aid, social_id, userpageid, social_date, social_uid, social_profilbild, social_nutzername, antwort, fields.username as u_username FROM
            (SELECT * FROM ".TABLE_PREFIX."socialantwort a 
            WHERE (a.social_id = '$postid')) AS antwort
            ,
            (SELECT * FROM ".TABLE_PREFIX."users) AS fields  
            WHERE social_uid = fields.uid  ORDER BY social_date ASC)
            UNION
            (SELECT social_aid, social_id, userpageid, social_date, social_uid, del_profilbild associal_profilbild, del_nutzername as social_nutzername, antwort, del_username as u_username FROM ".TABLE_PREFIX."socialantwort sp 
            WHERE (sp.userpageid = '$mpid') AND (social_id = '$postid') AND sp.social_uid NOT IN
            (SELECT uid FROM ".TABLE_PREFIX."users)) ORDER BY social_date ASC"); 
			
				
		//Daten der Antwort holen
			while($get_answer=$db->fetch_array($antwort)){
				$ant_kid = intval($get_answer['social_id']);
				$ant_avatar = htmlspecialchars_uni($get_answer['social_profilbild']);
				
				$ant_username = htmlspecialchars_uni($get_answer['social_nutzername']);
				
				if($ant_username == "") {
				 $ant_username = htmlspecialchars_uni($get_answer['u_username']);
				}
				
				//id des autors
				$antautor_id = intval($get_answer['social_uid']);
				//link zusammenbasteln	
				$antautor = '<a href=member.php?action=profile&uid='.$antautor_id.'&area=userpage>'.$ant_username.'</a>';
				
				//gibt es den user noch? 
				$deleteduser=$db->query("SELECT uid FROM ".TABLE_PREFIX."users WHERE uid = $antautor_id");
				
				$isdeleted = mysqli_num_rows($deleteduser);
				//wenn er gelöscht ist, dann kein link zur userpage
					if ($isdeleted == '0'){
							$antautor ="$ant_username";
					}
				
				$for_datum = $get_answer['social_date'];
				$ant_antwort = $parser->parse_message($get_answer['antwort'], $options);
				$ant_id = intval($get_answer['social_aid']);	
				$ant_datum = date("d.m.Y H:i:s",  strtotime($for_datum)
			);
			// wenn kein ava dann alternatives anzeigen
			if($ant_avatar=="") {
				$ant_avatar = "social/profil_leer.png";
			}
			
		//query für LikesAntwort - Username & Link zur Userpage
			$likersforvar=$db->query("
			SELECT likers.uid FROM ".TABLE_PREFIX."sociallikes likers 
			WHERE antwortid = '$ant_id'
			AND likers.uid IN (SELECT uid FROM ".TABLE_PREFIX."users)"
			);
			$classbox ="hidebox";
			$social_likeuser_tpl="";
			//Anzahl der Likes für antwort
				$likesAnzahlAntwort=$db->query("SELECT count(antwortid) AS Anzahl FROM ".TABLE_PREFIX."sociallikes WHERE antwortid = '$ant_id'");
				
				while($get_likes=$db->fetch_array($likesAnzahlAntwort)){
					$anzahl_likesantwort = intval($get_likes['Anzahl']);
				}  

				$var_like ="like";	
				//Antwort like - Usernmaen bekommen
				while($get_likersforvar=$db->fetch_array($likersforvar)){
					//$users = $get_likersforvar['user'];
					$uid= intval($get_likersforvar['uid']);
					$var_like ="like";		
					$likes = intval($_POST['anzahluserlikepost']);
					
					$this_username=$db->query("SELECT social_nutzername, username FROM ".TABLE_PREFIX."users WHERE ".TABLE_PREFIX."users.uid = '$uid'");
						while($get_name=$db->fetch_array($this_username)){
							$name_nutzername = htmlspecialchars_uni($get_name['social_nutzername']);
							$name_username = htmlspecialchars_uni($get_name['username']);
						if ($name_nutzername != '' || $name_nutzername != NULL ) {
						 	//$name ="socialname";
						 	$name = htmlspecialchars_uni($get_name['social_nutzername']);
							} else {
							$name = htmlspecialchars_uni($get_name['username']);
							//$name = htmlspecialchars_uni($get_name['social_nutzername']); 
						}
						}  
						
					$users ="<a href=\"member.php?action=profile&uid=".$uid."&area=userpage\">".$name."</a>";

					if($thisuser == $uid){
							$var_like ="dislike";
					} else {
							$var_like ="like";
					}
				
					if($likes == "0"){
						$classbox = "hidebox";
					} else {
						$classbox = "donthidebox";
					}
				//Ausgabe Usernamen für Antwort
				eval("\$social_likeuser_tpl .=\"".$templates->get("social_likeuser_tpl")."\";");
				}	
			
			//Template für die Ausgabe der Antworten
			eval("\$social_antwort .=\"".$templates->get("social_antwort")."\";");			
			} 
			
			
			$social_likeuser_tpl="";
			
			//query für LikesPost - Wieviele Likes hat der Beitrag?
			$likesanzahlpost=$db->query("
				SELECT count(postid) AS Anzahl FROM ".TABLE_PREFIX."sociallikes WHERE postid = '$postid'"
			);
			while($get_likespost=$db->fetch_array($likesanzahlpost)){
				$anzahl_likespost = $get_likespost['Anzahl'];
			}  
			
			// wem gefällt der Post?
			$likersforvarpost=$db->query("
				SELECT likers.uid FROM ".TABLE_PREFIX."sociallikes likers 
					where postid = '$postid'
					and likers.uid IN (SELECT uid FROM ".TABLE_PREFIX."users) 
			");
			
			$var_likepost ="like";	
			$classbox ="hidebox";
			while($get_likersvarpost=$db->fetch_array($likersforvarpost)){	
				//$users = $get_likersvarpost['user'];
				$uid= intval($get_likersvarpost['uid']);
				$likes = $_POST['anzahluserlike'];
				$this_username=$db->query("SELECT social_nutzername, username FROM ".TABLE_PREFIX."users WHERE ".TABLE_PREFIX."users.uid = '$uid'");
					while($get_name=$db->fetch_array($this_username)){
						$name_nutzername = htmlspecialchars_uni($get_name['social_nutzername']);
						$name_username = htmlspecialchars_uni($get_name['username']);
						if ($name_nutzername != '' || $name_nutzername != NULL ) {
						 	$name = htmlspecialchars_uni($get_name['social_nutzername']);
	
							} else {
							$name = htmlspecialchars_uni($get_name['username']);
						}
					}  
						
				$users ="<a href=\"member.php?action=profile&uid=".$uid."&area=userpage\">".$name."</a>";
				$var_likepost ="like";	
				if($thisuser == $uid){
					$var_likepost ="dislike";
				} else {
					$var_likepost ="like";
				}
				
				//damit nur eine Box angezeigt wird, wenn es wem gefällt
				if($likes == "0"){
					$classbox = "hidebox";
				} else {
					$classbox = "donthidebox";
				}
				
			//Ausgabe Usernamen
				eval("\$social_likeuser_tpl .=\"".$templates->get("social_likeuser_tpl")."\";");
			}
			
		
		//Template für die Ausgabe von Posts
		eval("\$social_post .=\"".$templates->get("social_post")."\";");
		}
	
	//das Formular um einen Post zu setzen
	eval("\$social_postform = \"".$templates->get("social_postform")."\";");
	

	//Gesamttemplate für Userpage postfunktion (ruft Postpage auf, die wiederrum die antworten aufruft)
	eval("\$social_page = \"".$templates->get("social_page")."\";");  
	//ende der Postfunktion 

	eval("\$page = \"".$templates->get('userpages_content')."\";");
	output_page($page); 
	die();
	}
}	

$plugins->add_hook("global_start", "hs_link");
function hs_link() 
{
	global $mybb, $db, $memprofile, $lang, $cache, $userpage_parser, $templates, $theme, $headerinclude, $header, $footer, $page, $parser, $userpagelink, $commentid, $social_link, $user, $headerlink_newsfeed, $lp_name, $lp_id, $headerlink_text_allg;
	
	$lang->load('userpages');
	$usergroups_cache = $cache->read("usergroups");
	$dieser_user = intval($mybb->user['uid']);
	$memprofile = $db->fetch_array($db->simple_select("users", "userpage, username, uid, social_nutzername", "uid = ".intval($mybb->user['uid'])), "userpage");
	
	$headerlink_newsfeed= '<a href="newsfeed.php"><img src="social/logo_100px.png" alt="logo" /></a>';
	$headerlink_text_allg = '<a href="newsfeed_allg.php">allgemein</a>';
	
	if ($memprofile['social_nutzername'] !== "" || $memprofile['social_nutzername'] !== "NULL") {
		$nutzername = htmlspecialchars_uni($memprofile['social_nutzername']);
	} else {
		$nutzername = htmlspecialchars_uni($mybb->user['username']);
	}
	
	if ($memprofile['uid'] == $mybb->user['uid']) {
	 $gotoedit = '<span class="smalltext"><a href="usercp.php?action=edituserpage"> - social network bearbeiten</a></span>';
	}

if($mybb->user['userpage'] == ""){
$social_link ="";
} else {
	$nutzerid = intval($mybb->user['uid']);
	$social_link = " - <a href=\"member.php?action=profile&uid=".$nutzerid."&area=userpage\">social network</a>";

}

}

$plugins->add_hook("global_start", "get_latest");
function get_latest() {
	global $mybb, $db, $memprofile, $lang, $cache, $userpage_parser, $templates, $theme, $headerinclude, $header, $footer, $page, $parser, $lp_name, $lp_post, $lp_link, $autor_link, $lp_name_ID, $lp_post_ID, $autor_link_ID, $lp_link_ID ;

	$memprofile = $db->fetch_array($db->simple_select("users", "userpage, username, uid, social_nutzername", "uid = ".intval($mybb->user['uid'])), "userpage");

require_once MYBB_ROOT."inc/class_parser.php";
$parser = new postParser;
	
$options = array(
	"allow_html" => $mybb->settings['userpages_html_active'],
	"allow_mycode" => $mybb->settings['userpages_mycode_active'],
	"allow_smilies" => 1,
	"allow_imgcode" => $mybb->settings['userpages_images_active'],
	"filter_badwords" => $mybb->settings['userpages_badwords_active'],
	"nl2br" => 1,
	"allow_videocode" => $mybb->settings['userpages_videos_active'],
	"me_username" => $memprofile['username'],
	);
	
//query um beitrag zu bekommen/ Username / datum d. letzten posts 
$get_lastpost = $db->query("SELECT * FROM ".TABLE_PREFIX."socialpost as sp, ".TABLE_PREFIX."users as u WHERE sp.uid = u.uid AND social_id = (SELECT max(social_id) FROM ".TABLE_PREFIX."socialpost AS tab)");


// Schleife um den letzten geschriebenen Post im Header oder wo auch immer anzuzeigen:
	while($lastpost = $db->fetch_array($get_lastpost)) {
		
		build_profile_link($lastpost['username'], $lastpost['uid']); // Username 
		$lp_post = $parser->parse_message($lastpost['social_post'], $options); // der Post
		$uid = intval($lastpost['uid']);
		$pageid = intval($lastpost['userpageid']);
		$postid = intval($lastpost['social_id']);
		$lp_name = "<a href=member.php?action=profile&uid=".$uid."&area=userpage>".htmlspecialchars_uni($lastpost['username'])."</a>";
		$autor_link = '<a href="member.php?action=profile&uid='.$uid.'&area=userpage">zum beitrag</a>'; // Zur Userpage des Autors
		$lp_link = '<a href="member.php?action=profile&uid='.$pageid.'&area=userpage#'.$postid.'">zum beitrag</a>'; //direkt zum beitrag des Autors
	}


/*
// Query um den letzten Beitrag einer bestimmten ID zu bekommen! X Ersetzen mit uid
	$get_lastpost_fromid = $db->query("SELECT * FROM ".TABLE_PREFIX."socialpost WHERE social_id =(SELECT max(social_id) FROM ".TABLE_PREFIX."socialpost AS tab WHERE uid = X)");
//	SELECT * FROM".TABLE_PREFIX."socialpost JOIN (SELECT * FROM ".TABLE_PREFIX."socialpost WHERE uid=2 AS TAB) WHERE social_ID = max(social_ID)");
	while($newlastpost = $db->fetch_array($get_lastpost_fromid)) {
		$lp_name_ID = htmlspecialchars_uni($newlastpost['username']);
		$lp_post_ID = $parser->parse_message($newlastpost['social_post'], $options);
		$uid_ID = intval($newlastpost['uid']);
		$pageid_ID = intval($newlastpost['userpageid']);
		$postid_ID = intval($newlastpost['social_id']);
		$autor_link_ID = '<a href="member.php?action=profile&uid='.$uid_ID.'&area=userpage">zum beitrag</a>';
		$lp_link_ID = '<a href="member.php?action=profile&uid='.$pageid_ID.'&area=userpage#'.$postid_ID.'">zum beitrag</a>';
	} 
*/
}

//Funktion um im Postbit auch Link zum Netzwerk anzeigen zu lassen
$plugins->add_hook("postbit", "get_post_social");
function get_post_social(&$post) { 
	global $mybb, $db, $cache, $templates, $theme;

if ($post['userpage'] != "") {
	//link zur userpage
	$userpagelink_post = '<a href="member.php?action=profile&uid='.$post['uid'].'&area=userpage">social network</a>';
	$post['social_text'] = "{$userpagelink_post}";
	//link mit logo
	$userpagelink_bild = '<a href="member.php?action=profile&uid='.$post['uid'].'&area=userpage"><img src="social/logo_80px.png" border="0" alt="logo" /></a>';
	$post['social_bild'] = "{$userpagelink_bild}";

	}
}

/*
*	End User Profile
*/

?>