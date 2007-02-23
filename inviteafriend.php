<?php
require_once "lib/init.php";
require_once "layout/error.php";
include "layout/inviteafriend.php";
require_once "prepare_profile_header.php";

MustLogIn() ; // member must login

$IdMember = $_SESSION["IdMember"];
$Email = GetParam("Email"); // find the email concerned 

$m = prepare_profile_header($IdMember,"",0) ; // This is the profile of the member who is going to send the mail

switch (GetParam("action")) {

	case "Send" : // Send the mail
		$MemberIdLanguage = GetDefaultLanguage($IdMember);
		$membername=AdminReadCrypted($m->FirstName)." ".AdminReadCrypted($m->LastName)." ".AdminReadCrypted($m->SecondName);
		$subj = ww("MailInviteAFriendSubject", $membername,$_SESSION['Username']);
		$urltosignup = "http://".$_SYSHCVOL['SiteName'] .$_SYSHCVOL['MainDir']. "signup.php" ;
		$MessageFormatted=$Message ;
		if (GetParam("JoinMemberPict")=="on") {
	  	   $rImage=LoadRow("select * from membersphotos where IdMember=".$IdMember." and SortOrder=0") ;
	  	   $MessageFormatted="<html>\n<head>\n" ;
	  	   $MessageFormatted.="<title>".$subj."</title>\n</head>\n" ;
	  	   $MessageFormatted.="<body>\n" ;
	  	   $MessageFormatted.="<table>\n" ;

	  	   $MessageFormatted.="<tr><td>\n" ;
	  	   $MessageFormatted.="<img alt=\"picture of ".$_SESSION['Username']."\" height=\"200px\" src=\"http://".$_SYSHCVOL['SiteName'].$rImage->FilePath."\" />" ;

	  	   $MessageFormatted.="</td>\n" ;
	  	   $MessageFormatted.="<td>\n" ;
	  	   $MessageFormatted.=ww("MailInviteAFriendText", $_SESSION['Username'], $Message, $urltosignup) ;
	  	   $MessageFormatted.="</td>\n" ;
	  	   $MessageFormatted.="</table>\n" ;
	  	   $MessageFormatted.="</body>\n" ;
	  	   $MessageFormatted.="</html>\n" ;
	  
	  	   $text=$MessageFormatted ;
		}
		else {
	  	   $text = ww("YouveGotAMailText", $_SESSION['Username'], $MessageFormatted, $urltosignup);
	 	}

		$_SERVER['SERVER_NAME'] = "www.bewelcome.org"; // to force because context is not defined

		if (!bw_mail($Email, $subj, $text, "", $_SYSHCVOL['MessageSenderMail'], $MemberIdLanguage, "html", "", "")) {
		   die("\nCannot send message to ".$Email."<br>\n");
		};

		DisplayResults($m,ww("MailSentToFriend",$Email)) ;
		LogStr("Sending a invite a friend mail to <b>".$Email."</b>","InviteAFriend") ;
		exit(0) ;
		break ;
}


DisplayForm($m) ;

?>
