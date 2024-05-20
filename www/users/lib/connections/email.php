<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;

require_once $_SERVER['DOCUMENT_ROOT'].'/settings_server.php';
require_once $GLOBALS['PROJECT_FOLDER_WITHOUT_SLASH'].'/vendor/autoload.php';

function sendMailFromGmail($recipient,$subj,$mess,$title) {
	if (substr($subj,0,8) != "[SSCAIT]") $subj = "[SSCAIT] ".trim($subj);
	$mail = new PHPMailer();
	$mail->isSMTP();
	$mail->Host = $GLOBALS["SMTP_HOST"];
	$mail->Port = $GLOBALS["SMTP_PORT"];

	//Set the encryption mechanism to use:
	// - SMTPS (implicit TLS on port 465) or
	// - STARTTLS (explicit TLS on port 587)
	$mail->SMTPSecure = ($mail->Port == '465') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

	$mail->SMTPAuth = $GLOBALS["SMTP_AUTH"];
	$mail->AuthType = 'XOAUTH2';

	$email = $GLOBALS["SMTP_USERNAME"]; // the email used to register google app
	$clientId = $GLOBALS["SMTP_GOOGLE_CLIENT_ID"];
	$clientSecret = $GLOBALS["SMTP_GOOGLE_CLIENT_SECRET"];
	$refreshToken = $GLOBALS["SMTP_GOOGLE_REFRESH_TOKEN"];

	//Create a new OAuth2 provider instance
	$provider = new Google(
		[
			'clientId' => $clientId,
			'clientSecret' => $clientSecret,
		]
	);

	//Pass the OAuth provider instance to PHPMailer
	$mail->setOAuth(
		new OAuth(
			[
				'provider' => $provider,
				'clientId' => $clientId,
				'clientSecret' => $clientSecret,
				'refreshToken' => $refreshToken,
				'userName' => $email,
			]
		)
	);

	$mail->setFrom($email, "SSCAIT <" . $GLOBALS["SMTP_USERNAME"] . ">");
	$mail->addAddress($recipient);
	$mail->isHTML(true);
	$mail->Subject = $subj;
	$mail->Body = "<html><head><title>".$title."</title></head><body>".$mess."</body></html>";

	//send the message, check for errors
	if (!$mail->send()) {
		echo 'Mailer Error: ' . $mail->ErrorInfo;
		error_log($mail->ErrorInfo);
		return FALSE;			
	} else {
		echo 'Message sent!';
		return TRUE;
	}
}

?>
