<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/settings_server.php';

function sendMailFromGmail($recipients,$subj,$mess) {
	if (substr($subj,0,8) != "[SSCAIT]") $subj = "[SSCAIT] ".trim($subj);
	require_once "Mail.php";
	$headers["MIME-Version"] = '1.0';
	$headers["Content-type"] = "text/html; charset=iso-8859-1";
	$headers["From"] = "SSCAIT <sscait@gmail.com>";
	$headers["To"] = $recipients;
	$headers["Subject"] = $subj;
	$mailmsg = "<html><body>".$mess."</body></html>";
	/* SMTP server name, port, user/passwd */
	$smtpinfo = array();
	$smtpinfo["host"] = $GLOBALS["SMTP_HOST"];
	$smtpinfo["port"] = $GLOBALS["SMTP_PORT"];
	$smtpinfo["auth"] = $GLOBALS["SMTP_AUTH"];
	$smtpinfo["username"] = $GLOBALS["SMTP_USERNAME"];
	$smtpinfo["password"] = $GLOBALS["SMTP_PASSWORD"];
	/* Create the mail object using the Mail::factory method */
	$mail_object = Mail::factory("smtp", $smtpinfo);
	/* Ok send mail */
	$mail_object->send($recipients, $headers, $mailmsg);
}


?>
