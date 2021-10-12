<?php 
require_once "Auth.php";
require_once "Util.php";

$auth = new Auth();
$db_handle = new DBController();
$util = new Util();

// Get Current date, time
$current_time = time();
$current_date = date("Y-m-d H:i:s", $current_time);

// Set Cookie expiration for 1 month
$cookie_expiration_time = $current_time + (30 * 24 * 60 * 60);  // for 1 month

$isLoggedIn = false;

// Check if loggedin session and redirect if session exists
if (! empty($_SESSION["member_id"])) {
    $isLoggedIn = true;
}
// Check if loggedin session exists
else if (! empty($_COOKIE["member_login"]) && ! empty($_COOKIE["random_password"]) && ! empty($_COOKIE["random_selector"])) {
    // Initiate auth token verification directive to false
    $isPasswordVerified = false;
    $isSelectorVerified = false;
    $isExpiryDateVerified = false;
    $isAuthValidatorVerified = false;
    $isAuthExpiryDateVerified = false;

    
    // Get token for username
    $userToken = $auth->getTokenByUsername($_COOKIE["member_login"],0);
    if ($userAuthToken = $auth->getAuthTokenBySelector($_COOKIE["selector"])){
        // Validate validator cookie with database
        if (password_verify($_COOKIE["validator"], $userAuthToken[0]["hashedValidator"])) {
        $isAuthValidatorVerified = true;
        }
         // check cookie expiration by date
        if($userAuthToken[0]["expires"] >= $current_date) {
        $isAuthExpiryDateVerified = true;
        }
        };
    
    // Validate random password cookie with database
    if (password_verify($_COOKIE["random_password"], $userToken[0]["password_hash"])) {
        $isPasswordVerified = true;
    }
    
    // Validate random selector cookie with database
    if (password_verify($_COOKIE["random_selector"], $userToken[0]["selector_hash"])) {
        $isSelectorVerified = true;
    }

    // check cookie expiration by date
    if($userToken[0]["expiry_date"] >= $current_date) {
        $isExpiryDateVerified = true;
    }
    

    if (!empty($userToken[0]["id"]) && $isPasswordVerified && $isSelectorVerified && $isExpiryDateVerified
       && $isAuthValidatorVerified && $isAuthExpiryDateVerified ) {
       $isLoggedIn = true;
       } 
    // Else, mark the token as expired and clear cookies   
    else {
        if(!empty($userToken[0]["id"])) {
            $auth->markAsExpired($userToken[0]["id"]);
        }
        if(!empty($userAuthToken[0]["id"])) {
            $auth->deleteAuthToken($userToken[0]["id"]);
        }
        // clear cookies
        $util->clearAuthCookie();
        }
}
?>