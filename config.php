<?php
header("Content-Type: text/html; charset=utf-8");
header("ACCESS-CONTROL-ALLOW-ORIGIN: *");
header("ACCESS-CONTROL-ALLOW-METHODS: *");

require_once __DIR__ . '/Facebook/autoload.php';

session_start();

define('APP_ID','356811972705689');
define('APP_SECRET','1346c0ce59ae39eef9792051fac6fb96');

$fb = new \Facebook\Facebook([
    'app_id' => APP_ID,
    'app_secret' => APP_SECRET,
    'default_graph_version' => 'v2.5',
  ]);

$helper = $fb->getRedirectLoginHelper();

try {
if (isset($_SESSION['facebook_access_token'])) {
  $accessToken = $_SESSION['facebook_access_token'];
} else {
    $accessToken = $helper->getAccessToken();
  }
} catch(Facebook\Exceptions\facebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}

if (isset($accessToken)) {
  if (isset($_SESSION['facebook_access_token'])) {
    $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
} else {
    // getting short-lived access token
    $_SESSION['facebook_access_token'] = (string) $accessToken;
    // OAuth 2.0 client handler
    $oAuth2Client = $fb->getOAuth2Client();
    // Exchanges a short-lived access token for a long-lived one
    $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
    $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
    // setting default access token to be used in script
    $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
}

// redirect the user to the profile page if it has "code" GET variable
if (isset($_GET['code'])) {
  header('Location: profile.php');
}

// getting basic info about user
try {
  $profile_request = $fb->get('/me?fields=name,first_name,last_name,email');
  $requestPicture = $fb->get('/me/picture?redirect=false&height=200'); //getting user picture
  $picture = $requestPicture->getGraphUser();
  $profile = $profile_request->getGraphUser();
  $fbid = $profile->getProperty('id');           // To Get Facebook ID
  $fbfullname = $profile->getProperty('name');   // To Get Facebook full name
  $fbemail = $profile->getProperty('email');    //  To Get Facebook email
  $fbpic = "<img src='".$picture['url']."' class='img-rounded'/>";
  # save the user nformation in session variable
  $_SESSION['fb_id'] = $fbid.'</br>';
  $_SESSION['fb_name'] = $fbfullname.'</br>';
  $_SESSION['fb_email'] = $fbemail.'</br>';
  $_SESSION['fb_pic'] = $fbpic.'</br>';
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  session_destroy();
  // redirecting user back to app login page
  header("Location: ./");
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
  }

} else {
  // replace your website URL same as added in the developers.Facebook.com/apps e.g. if you used http instead of https and you used            
  $loginUrl = $helper->getLoginUrl('https://mohsinlodhi4.github.io/graph-api.github.io/');
  // echo '<a  href="' . $loginUrl . '">Log in with Facebook!</a>';
  echo $loginUrl;
}





// From Github
  /*
  try {
    // Get the \Facebook\GraphNodes\GraphUser object for the current user.
    // If you provided a 'default_access_token', the '{access-token}' is optional.
    $response = $fb->get('/me', '{access-token}');
  } catch(\Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
  } catch(\Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
  }
  
  $me = $response->getGraphUser();
  echo 'Logged in as ' . $me->getName();
*/
?>
