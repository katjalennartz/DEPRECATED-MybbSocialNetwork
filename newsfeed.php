<?php 
define("IN_MYBB", 1);

require("global.php");
	global $db, $mybb, $lang, $users, $templates, $parser, $theme, $memprofile, $theme, $userfields, $customfields, $profilefields, $field_hidden, $bgcolor, $alttrow, $socialnetworklink, $newsfeedlink;

require_once MYBB_ROOT."inc/class_parser.php";
$parser = new postParser;

if ($mybb->user['uid'] == 0)
{
	error_no_permission();
}

//array für parser
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
		
//welcher user ist gerad online
$thisuser = intval($mybb->user['uid']); 

//Name vom nutzer der online ist, der beim social work genutzt werden soll, wenn ausgefüllt
$benutzername = htmlspecialchars_uni($mybb->user['social_nutzername']);

if($benutzername == ""){
	$benutzername = htmlspecialchars_uni($mybb->user['username']);
}
if($benutzername == "NULL"){
	$benutzername = htmlspecialchars_uni($mybb->user['username']);
}    

$haspage = $db->fetch_field($db->simple_select("users","userpage", "uid = '$thisuser'"), "userpage");

if ($haspage != "") {
 $newsfeedlink = " - <a href=\"newsfeed.php\">social network newsfeed</a>";
}

if ($_POST['ant_senden'] != '' && $_POST['antwort'] != '' && $mybb->user['uid'] != 0){
		//Array erstellen um Daten von Antwort in die Datenbank zu speichern
		$ant_datum = sprintf("%d-%d-%d %d:%d:%d", $_POST['ant_year'], $_POST['ant_month'], $_POST['ant_day'], $_POST['ant_std'], 
		$_POST['ant_min'], $_POST['ant_sek']);
		
		$whichpage = $mybb->input['get_userpageid'];
		$userpagelink ="<a href=\"member.php?action=profile&uid=".$mybb->user['uid']."&area=userpage\">".$benutzername."</a>";
		
		$antworten = array(
			"social_date" =>$ant_datum,
			"userpageid" =>intval($whichpage),
			"social_uid" => intval($mybb->user['uid']),
			"antwort" => $db->escape_string($mybb->input['antwort']),
			"social_id"=>intval($mybb->input['get_social']),
			"del_username" =>$db->escape_string($mybb->user['username']),
			"del_nutzername" =>  $db->escape_string($mybb->user['social_nutzername'])
			); 
		//speichern
		$db->insert_query("socialantwort", $antworten);
	
// Variablen für Benachrichtigung
	//Post auf den geantwortet wird
	$get_postid=intval($mybb->input['postid']);
	//auf wessen antwort wird gepostet ? 
	$autor = intval($mybb->input['postautorid']);
	$whichpage = intval($mybb->input['postautorid']);
	
	$get_ansid=$db->query("SELECT max(social_aid) AS id FROM ".TABLE_PREFIX."socialantwort");
	while($pid = $db->fetch_array($get_ansid)) {
	$ansid = intval($pid['id']);
	}
	
	//PN an User, auf dessen Post geantwortet wurde. Will der Autor PNs?
	$pn_post2 = $db->fetch_field($db->simple_select("users","social_postcheck", "uid = '$autor'"), "social_postcheck");
	//nur wenn Benachrichtigung erlaubt
	if($pn_post2 == "1"){		
		$pm2 = array(
			'subject' => 'social network - Antwort auf einen Post',
			'message' => htmlspecialchars_uni($mybb->user['username'])." hat eine <a href=\"member.php?action=profile&uid=".$autor."&area=userpage#a".$ansid."\">Antwort</a> auf deinen Post geschrieben.",
			'touid' => $autor,
			'language' => $user['language'],
			'language_file' => 'usercp'
		);	
		if ($thisuser != $autor) {
			send_pm($pm2, -1, true);
		}
	}
	
	//Benachrichtigung für User wird erwähnt:
	$antwort = htmlspecialchars_uni($db->escape_string($mybb->input['antwort']));	
	$alert_query=$db->query("SELECT username,uid FROM ".TABLE_PREFIX."users");

	// Alle User des Forums in ein Array speichern, Index des Arrays ist uid
	$arruser = array();
	while($alert = $db->fetch_array($alert_query)) {
		$username = htmlspecialchars_uni($alert['username']);
		$uid = intval($alert['uid']);
		$arruser[$uid] = $username;
	}
	
	//Dieses Array durchgehen und schauen ob der Nutzer im Post erwähnt wurde
	foreach ($arruser as $uid => $mentioned ) {
		$createsearchstring = "@".$mentioned;
		$tofind = "/$createsearchstring/";
		//wenn er gefunden wurde -> Benachrichtigung
		if( preg_match($tofind, $antwort) ) {
			$pn_named = $db->fetch_field($db->simple_select("users","social_namedcheck", "uid = '$uid'"), "social_namedcheck");
			//nur wenn Benachrichtigung erlaubt
				if($pn_named == "1"){	
					$pm = array(
					'subject' => 'Mentioned',
					'message' => $mybb->user['username']." hat dich in einer <a href=\"member.php?action=profile&uid=".$whichpage."&area=userpage#a".$postid."\">Antwort</a> erwähnt.",
					'touid' => $uid,
					'language' => $user['language'],
					'language_file' => 'usercp'
				);
				send_pm($pm, -1, true);
			}
		}
	}
	redirect("newsfeed.php", "Newsfeed");
}      

//Gefällt mir Funktion
//Post liken
if ($_POST['like'] == 'like'){
	$id= intval($mybb->user['uid']); 
	$name = htmlspecialchars_uni($mybb->user['username']);
	$userpagelink ="<a href=\"member.php?action=profile&uid=".$id."&area=userpage\">".$name."</a>";
	$postautor = $mybb->input['postautorid'];
	$likepostid = $mybb->input['postid'];
	
	$likes = array(
		"user" => $userpagelink,
		"postid"=> $likepostid,
		"uid"=> $mybb->user['uid']
	);

	$db->insert_query("sociallikes", $likes); 
	
	$pn_likes = $db->fetch_field($db->simple_select("users","social_likecheck", "uid = '$postautor'"), "social_likecheck");
	//nur wenn Benachrichtigung erlaubt
	if($pn_likes == "1"){			
		$pm = array(
			'subject' => 'social network Like',
			'message' => $mybb->user['username']." gefällt dein <a href=\"member.php?action=profile&uid=".$postautor."&area=userpage#".$likepostid."\">Post</a>.",
			'touid' => $postautor,
			'language' => $user['language'],
			'language_file' => 'usercp'
		);
		send_pm($pm, -1, true);
	}	
	redirect("newsfeed.php", "Newsfeed");
}
//like zurücknehmen
if ($_POST['like'] == 'dislike'){
	$dislike_postid=$mybb->input['postid'];
	$db->query("DELETE FROM ".TABLE_PREFIX."sociallikes WHERE uid = '$thisuser' && postid = '$dislike_postid'");
	redirect("newsfeed.php", "Newsfeed");
} 


//Antwort liken
if ($_POST['likeantwort'] == 'like'){
	$user_id= intval($mybb->user['uid']); 
	$name = htmlspecialchars_uni($mybb->user['username']);
	$userpagelink ="<a href=\"member.php?action=profile&uid=".$user_id."&area=userpage\">".$name."</a>";
	$likeantwortid = $_POST['l_aid'];
	$likepostautor= $_POST['antautorname'];
	
	$likes = array(
		"user" => $userpagelink,
		"antwortid"=> $_POST['l_aid'],
		"uid"=> $mybb->user['uid']
	);
	$db->insert_query("sociallikes", $likes); 
	
	//PM für Gefällt mir	
	$pn_likes = $db->fetch_field($db->simple_select("users","social_likecheck", "uid = '$likepostautor'"), "social_likecheck");
	//nur wenn Benachrichtigung erlaubt
	if($pn_likes == "1"){		
	$pm = array(
		'subject' => 'social network Like',
		'message' => $mybb->user['username']." gefällt deine <a href=\"member.php?action=profile&uid=".$user_id."&area=userpage#a".$likeantwortid."\">Antwort</a>.",
		'touid' => $likepostautor,
		'language' => $user['language'],
		'language_file' => 'usercp'
	);
	send_pm($pm, -1, true);
}
	redirect("newsfeed.php", "Newsfeed");
}

//like zurücknehmen
if ($_POST['likeantwort'] == 'dislike'){
	$id = $_POST['l_aid']; 
	$db->query("DELETE FROM ".TABLE_PREFIX."sociallikes WHERE uid = '$thisuser' && antwortid = '$id'");
	redirect("newsfeed.php", "Newsfeed");
} 



//profilbild vom aktuellen user laden
$ownavatar = $db->fetch_field($db->simple_select("users","social_profilbild", "uid = '$thisuser'"), "social_profilbild");

//startpage
if (isset($_GET["page"])) {
	$page  = intval($_GET["page"]);
} else { 
	$page=1; 
}; 

//wieviele ergebnisse pro seite
$per_page=10; 
$start_from = intval(($page-1) * $per_page);

$count_sql = $db->query("SELECT count(getposts.social_id) cnt FROM 
		(SELECT uid, social_profilbild, social_nutzername, username u_username FROM ".TABLE_PREFIX."users) AS fields JOIN
		(
			(SELECT social_id,uid,social_date,userpageid,social_post FROM ".TABLE_PREFIX."socialpost up,
			(SELECT isfriend FROM ".TABLE_PREFIX."socialfriends WHERE uid = '$thisuser') AS friends
			WHERE up.uid = friends.isfriend) 
			UNION
			
		(SELECT social_id,uid,social_date,userpageid,social_post FROM ".TABLE_PREFIX."socialpost WHERE uid = '$thisuser') 
		) AS getposts
		ON getposts.uid = fields.uid ORDER BY social_date DESC");
$row = $count_sql->fetch_assoc();
$total_pages = ceil($row['cnt'] / $per_page);
for ($i=1; $i<=$total_pages; $i++) {  // print links for all pages

$multipage .= "<a href='newsfeed.php?page=".$i."'>".$i."</a> - ";

}; 

//Query um die eigenen POSTS und die seiner Freunde zu sehen
$social_postquery=$db->query("
		SELECT * FROM 
		(SELECT uid, social_profilbild, social_nutzername, username u_username FROM ".TABLE_PREFIX."users) AS fields JOIN
		(
			(SELECT social_id,uid,social_date,userpageid,social_post FROM ".TABLE_PREFIX."socialpost up,
			(SELECT isfriend FROM ".TABLE_PREFIX."socialfriends WHERE uid = '$thisuser') AS friends
			WHERE up.uid = friends.isfriend) 
			UNION
			
		(SELECT social_id,uid,social_date,userpageid,social_post FROM ".TABLE_PREFIX."socialpost WHERE uid = '$thisuser') 
		) AS getposts
		ON getposts.uid = fields.uid ORDER BY social_date DESC LIMIT ".$start_from.", ".$per_page."");

//Get Posts
while($get_socialposts=$db->fetch_array($social_postquery)) {	

		$social_newsfeedant="";
		$userpageid = intval($get_socialposts['userpageid']);
		$postid = intval($get_socialposts['social_id']);
		$postautorid = intval($get_socialposts['uid']);
		$p_username = htmlspecialchars_uni($get_socialposts['username']);
		$avatar = htmlspecialchars_uni($get_socialposts['social_profilbild']);
		$for_pdatum = $get_socialposts['social_date'];
		$post = $parser->parse_message($get_socialposts['social_post'], $options);
		$social_datum = date("d.m.Y H:i:s",  strtotime($for_pdatum));
	
		if($avatar=="") {
			$avatar = "social/profil_leer.png";
		}
		
		$p_username = htmlspecialchars_uni($get_socialposts['social_nutzername']);
		
		if ($p_username==""){
		$p_username=htmlspecialchars_uni($get_socialposts['u_username']);
		}
		if
		($p_username=="NULL"){
		$p_username=htmlspecialchars_uni($get_socialposts['u_username']);
		}
		$username ="<a href=member.php?action=profile&uid=".$postautorid."&area=userpage>".$p_username."</a>";

		$antwort=$db->query("SELECT * FROM
		(SELECT * FROM ".TABLE_PREFIX."socialantwort a WHERE a.social_id = '$postid') AS antwort
			JOIN 
		(SELECT uid, social_profilbild, social_nutzername, username u_username FROM ".TABLE_PREFIX."users) AS fields ON 
			social_uid = fields.uid  ORDER BY social_date ASC");
			
		//Daten der Antwort holen
			while($get_answer=$db->fetch_array($antwort)){
				$ant_kid = intval($get_answer['social_id']);
				$ant_avatar = htmlspecialchars_uni($get_answer['social_profilbild']);
				$antautor =  intval($get_answer['social_uid']);
				$ant_username_a = htmlspecialchars_uni($get_answer['social_nutzername']);
				
				if($ant_username_a == "") {
				 $ant_username_a = htmlspecialchars_uni($get_answer['u_username']);
				}
				if($ant_username_a == "NULL") {
				 $ant_username_a = htmlspecialchars_uni($get_answer['u_username']);
				}
				$ant_username ="<a href=member.php?action=profile&uid=".$antautor."&area=userpage>".$ant_username_a."</a>";
				$for_datum = $get_answer['social_date'];
				$ant_antwort = $parser->parse_message($get_answer['antwort'], $options);
				$ant_id = intval($get_answer['social_aid']);	
				$ant_datum = date("d.m.Y H:i:s",  strtotime($for_datum)
			);
			
			if($ant_avatar=="") {
				$ant_avatar = "social/profil_leer.png";
			}
			
			
			
		//query für LikesAntwort - Username & Link zur Userpage
			$likersforvar=$db->query("SELECT uid, user FROM ".TABLE_PREFIX."sociallikes WHERE antwortid = '$ant_id'");
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
					$users = htmlspecialchars_uni($get_likersforvar['user']);
					$uid= intval($get_likersforvar['uid']);
					$var_like ="like";		
					$likes = $_POST['anzahluserlikepost'];
				
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
			eval("\$social_newsfeedant .=\"".$templates->get("social_newsfeedant")."\";");			
			} 
		
		
		
	//Likes Post
	$social_likeuser_tpl="";
			//query für LikesPost - Wieviele Likes hat der Beitrag?
			$likesanzahlpost=$db->query("
				SELECT count(postid) AS Anzahl FROM ".TABLE_PREFIX."sociallikes WHERE postid = '$postid'"
			);
			while($get_likespost=$db->fetch_array($likesanzahlpost)){
				$anzahl_likespost = intval($get_likespost['Anzahl']);
			}  
			
			// wem gefällt der Post?
			$likersforvarpost=$db->query("
				SELECT uid,user FROM ".TABLE_PREFIX."sociallikes WHERE postid = '$postid'"
			);
			
			$var_likepost ="like";	
			$classbox ="hidebox";
			while($get_likersvarpost=$db->fetch_array($likersforvarpost)){	
				$users = $get_likersvarpost['user'];
				$uid= $get_likersvarpost['uid'];
				$likes = $_POST['anzahluserlike'];
				
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
	
	eval("\$social_newsfeedpost .= \"".$templates->get("social_newsfeedpost")."\";");        
	}


$socialnetworklink="";
//TO DO $grab uid zuordnen von welcem user der angezeigte post ist
$grabUserpage =$db->query("SELECT uid FROM ".TABLE_PREFIX."users WHERE uid = '$grab_uid' AND NOT userpage = ''");
while($hasuserpage = $db->fetch_array($grabUserpage)){
	$socialnetworklink ='<a href="member.php?action=profile&uid='.$hasuserpage['uid'].'&area=userpage"><img src="social/logo_50px.png" border="0"></a>';
}

eval("\$social_newsfeed .= \"".$templates->get("social_newsfeed")."\";");        
output_page($social_newsfeed);
?>