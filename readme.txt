###'ACHTUNG #####
funktioniert ab mybb version 1.8.24 nicht mehr.
-> Bitte Social Network 2.0 nutzen :) 
https://github.com/katjalennartz/socialnetwork_2.0


Orginalplugin: 
Userpages for MyBB
Erstellt von euantor / Codicious

Erweitert von risuena / 
http://lslv.de (Nathan Broderick) 
http://storming-gates.de/member.php?action=profile&uid=39 (risuena)


Support und mehr Infos gibts hier: 
http://storming-gates.de/showthread.php?tid=14707

Tipps und Tricks

Im Ordner /social findet ihr alle Grafiken, die ihr nach belieben austauschen/anpassen whatever könnt.

/social.css hier könnt ihr das Aussehen des Netzwerks anpassen. 

_________________________________

Die Variable für den Link zum Newsfeed sind:

// Newsfeed mit Posts von Freunden des Users
	{$headerlink_newsfeed} - mit logo 150px
	{$social_link} - ohne logo
	
//link zum allgemeinen Newsfeed
	{$headerlink_text_allg} - link zum allgemeinen 



	Autorname: {$lp_name}
	Post: {$lp_post} 
	Link zu page des Autors: {$autor_link}
	Link zum Beitrag: {$lp_link}



_________________________________

Um den aktuellsten Post einer BESTIMMTEN ID im header anzuzeigen, könnt ihr folgende Variablen nutzen:
ACHTUNG die ID muss in upload/inc/plugins/userpages.php angepasst werden!!! 
Die Funktion ist erst einmal auskommentiert!

	Gehe zu Zeile 2249:
	Entferne /*
	
	Gehe zu Zeile 2251 uid = X anpassen. 
	(X = user id des Nutzers von dem der Post angezeigt werden soll ) 

	Gehe zu Zeile 2262 
	Entferne */  


Variablen:

	Autorname: {$lp_name_ID} 
	Post {$lp_post_ID} 
	Link zu page des Autors:  {$autor_link_ID} 
	Link zum Beitrag: {$lp_link_ID}


_________________________________

Variable für Postbit:

Mit Text 'social network'
	{$post['social_text']}
	
Mit Logo 
	{$post['social_bild']}
	
__________________________________

Mehr Infos zum Plugin:


Die zip Datei sollte folgende Dateien enthalten:
- readme.txt  
- update.txt 
- upload/newsfeed.php
- upload/social.css
- social_userlist.php
- newsfeed_allg.php

- upload/social/dislike.png
- upload/social/like.png
- upload/social/logo_50px.png
- upload/social/logo_50px2.png
- upload/social/logo_80px.png
- upload/social/logo_100px.png
- upload/social/logo_150px.png
- upload/social/logo_300px.png
- upload/social/profil_leer.png

- upload/inc/languages/deutsch_du/userpages.lang.php
- upload/inc/languages/deutsch_du/admin/userpages.lang.php
- upload/inc/languages/deutsch_sie/userpages.lang.php
- upload/inc/languages/deutsch_sie/admin/userpages.lang.php
- upload/inc/languages/english/userpages.lang.php
- upload/inc/languages/english/admin/userpages.lang.php

- upload/inc/plugins/userpages.php




