<?php 

// A helper to send an email.
function mailPetition($addr, $username, $response, $file_name) {
	/** Send an email 
	*
	* Args:
	* $addr (string): An email address
	* $user (string): A user's name
	* $response (Array): An array of information to be sent to the
	* 	user.
	* $file_name (string): The name of a file that will be emailed to the user
	**/
	require_once("vendor/autoload.php");
	//require_once("config.php");
	global $sendGridApiKey;
	global $dataDir;
	$msg = "Thank you for using the Expungement Generator.\r\n";
	$msg .= json_encode($response);
	$from = new SendGrid\Email("Expungement Generator API", "mhollander@clsphila.org");
	$subject = "EG API Generator Search";
	$to = new SendGrid\Email($username, $addr);
	$content = new SendGrid\Content("text/plain", $msg);
	$mail = new SendGrid\Mail($from, $subject, $to, $content);
	$file_path = join(DIRECTORY_SEPARATOR, array($dataDir, $file_name));
	if ( !is_null($file_name) && file_exists($file_path) ) {
		$petition = new SendGrid\Attachment();
		$petition->setContent(base64_encode(file_get_contents($file_path)));
		$petition->setType("application/zip");
		$petition->setFilename("GeneratedPetition");
		$petition->setDisposition("attachment");
		$mail->addAttachment($petition);
	}
	$sg = new \SendGrid($sendGridApiKey);
	if ( is_null($sg) ) {throw new Exception("sg is null");}
	if ( is_null($sg->client) ) {throw new Exception("sg->client is null");}
	#print_r($sg);
	$response = $sg->client->mail()->send()->post($mail);
}


//Helper to identify where an email should go.
function mailDestination($request) {
	// Given a request object, if the fields emailAddressField and 
	// emailDomainField are set and have valid characters,
	// use them to build an email address. Otherwise return the 'current_user' from the request object.
	//error_log("building email");
	if ( isset($request['emailAddressField']) && preg_match( '/^[a-z]{0,20}$/i', $request['emailAddressField'] )===1 
			&& isset($request[$request['emailAddressField']]) ) {
		//error_log("emailaddressfield is " . $request['emailAddressField']);
		//emailAddressField is valid, so we can use it for the emailAddress
		$emailAddress = $request[$request['emailAddressField']];
		if ( isset($request['emailDomain'] ) && preg_match('/^[a-z\-\.]{0,30}\.(org|com|net)$/', $request['emailDomain'])===1 ) {
			//emailDomain is valid (something like casemanager.com)
			error_log("emailDomain is " . $request['emailDomain']);
			$emailDomain = $request['emailDomain'];
			return($emailAddress . "@" . $emailDomain);
		}
	}
	//something failed. Return the userid.
	return($request['current_user']);
}

?>

