<?php
require_once 'Instagram.php';

/**
 * Configuration params, make sure to write exactly the ones
 * instagram provide you at http://instagr.am/developer/
 */
$config = array(
        'client_id' => 'e8d6b06f7550461e897b45b02d84c23e',
        'client_secret' => '2357fc69da344800acef2592ef647491',
        'grant_type' => 'authorization_code',
        'redirect_uri' => 'http://mauriciocuenca.com/qnktwit/',
     );
	 
/**
 * This is how a wrong response looks like
 * array(1) { ["InstagramOAuthToken"]=> string(89) "{"code": 400, "error_type": "OAuthException", "error_message": "No matching code found."}" }
 */
session_start();
if (isset($_SESSION['InstagramAccessToken']) && !empty($_SESSION['InstagramAccessToken'])) {
    header('Location: callback.php');
    die();
}

// Instantiate the API handler object
$instagram = new Instagram($config);
//Setup subscription
if(isset($rs[0]["access_token"])) {
	$instagram->setAccessToken($_SESSION['InstagramAccessToken']);
	if(isset($_GET['list'])) {
		$subscriptions = $instagram->listSubscriptions();
	} else if(isset($_GET['delete'])) {
		$subscriptions = $instagram->deleteSubscription(array(
			"object" => "all"
		));
	} else  {
		$subscriptions = $instagram->createSubscription(array(
			// "object" => "user",
			// "object_id" => "371870962",
			"object" => "tag",
			"object_id" => "mydowntown",
			"aspect" => "media",
			"callback_url" => $config['instagram']['redirect_uri']."subscriptions.php",
		));
	}

	print_r($subscriptions);
}
