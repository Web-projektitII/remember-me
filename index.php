<?php
session_start();
require_once "authCookieSessionValidate.php";

/*Proactively Secure Long-Term User Authentication
https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
What follows is our proposed strategy for handling "remember me" cookies in a web application without leaking any useful information (even timing information) to an attacker, while still being fast and efficient (to prevent denial of service attacks).
Our proposed strategy deviates from the above simple token-based automatic login system in one crucial way: Instead of only storing a random token in a cookie, we store selector:validator.
selector is a unique ID to facilitate database look-ups, while preventing the unavoidable timing information from impacting security. (This is preferable to simply using the database id field, which leaks the number of active users on the application.)

CREATE TABLE `auth_tokens` (
    `id` integer(11) not null UNSIGNED AUTO_INCREMENT,
    `selector` char(12),
    `hashedValidator` char(64),
    `userid` integer(11) not null UNSIGNED,
    `expires` datetime,
    PRIMARY KEY (`id`)
);

On the database side of things, the validator is not stored wholesale; instead, the SHA-256 hash of validator is stored in the database, while the plaintext is stored (with the selector) in the user's cookie. With this fail-safe in place, if somehow the auth_tokens table is leaked, immediate widespread user impersonation is prevented.
The automatic login algorithm looks something like:

    Separate selector from validator.
    Grab the row in auth_tokens for the given selector. If none is found, abort.
    Hash the validator provided by the user's cookie with SHA-256.
    Compare the SHA-256 hash we generated with the hash stored in the database, using hash_equals().
    If step 4 passes, associate the current session with the appropriate user ID.*/



if ($isLoggedIn) {
    $util->redirect("dashboard.php");
}

if (! empty($_POST["login"])) {
    $isAuthenticated = false;
    
    $username = $_POST["member_name"];
    $password = $_POST["member_password"];
    
    $user = $auth->getMemberByUsername($username);
    if (password_verify($password, $user[0]["member_password"])) {
        $isAuthenticated = true;
    }
    
    if ($isAuthenticated) {
        $_SESSION["member_id"] = $user[0]["member_id"];
        $userid = $user[0]["member_id"];
        
        // Set Auth Cookies if 'Remember Me' checked
        if (! empty($_POST["remember"])) {
            /* setcookie($name,$value,$expire,$path,$domain,$secure,$httponly) */
            setcookie("member_login",$username,$cookie_expiration_time,NULL,NULL,true,true);
            
            $random_password = $util->getToken(16);
            $validator = $util->getToken(16);
            setcookie("random_password",$random_password,$cookie_expiration_time,NULL,NULL,true,true);
            setcookie("validator",$validator,$cookie_expiration_time,NULL,NULL,true,true);
            
            $random_selector = $util->getToken(32);
            $selector = $util->getToken(12);
            setcookie("random_selector",$random_selector,$cookie_expiration_time,NULL,NULL,true,true);
            setcookie("selector",$selector,$cookie_expiration_time,NULL,NULL,true,true);
            
            $random_password_hash = password_hash($random_password, PASSWORD_DEFAULT);
            $random_selector_hash = password_hash($random_selector, PASSWORD_DEFAULT);
            $hashedValidator = password_hash($validator, PASSWORD_DEFAULT);

            $expiry_date = date("Y-m-d H:i:s", $cookie_expiration_time);
            
            // mark existing token as expired
            $userToken = $auth->getTokenByUsername($username, 0);
            if (! empty($userToken[0]["id"])) {
                $auth->markAsExpired($userToken[0]["id"]);
            }
            // Insert new token
            $auth->insertToken($username, $random_password_hash, $random_selector_hash, $expiry_date);
            $auth->insertAuthToken($userid,$hashedValidator,$selector,$expiry_date);
        } else {
            $util->clearAuthCookie();
        }
        $util->redirect("dashboard.php");
    } else {
        $message = "Invalid Login";
    }
}
?>
<style>
body {
    font-family: Arial;
}

#frmLogin {
    padding: 20px 40px 40px 40px;
    background: #d7eeff;
    border: #acd4f1 1px solid;
    color: #333;
    border-radius: 2px;
    width: 300px;
}

.field-group {
    margin-top: 15px;
}

.input-field {
    padding: 12px 10px;
    width: 100%;
    border: #A3C3E7 1px solid;
    border-radius: 2px;
    margin-top: 5px
}

.form-submit-button {
    background: #3a96d6;
    border: 0;
    padding: 10px 0px;
    border-radius: 2px;
    color: #FFF;
    text-transform: uppercase;
    width: 100%;
}

.error-message {
    text-align: center;
    color: #FF0000;
}
</style>

<form action="" method="post" id="frmLogin">
    <div class="error-message"><?php if(isset($message)) { echo $message; } ?></div>
    <div class="field-group">
        <div>
            <label for="login">Username</label>
        </div>
        <div>
            <input name="member_name" type="text"
                value="<?php if(isset($_COOKIE["member_login"])) { echo $_COOKIE["member_login"]; } ?>"
                class="input-field">
        </div>
    </div>
    <div class="field-group">
        <div>
            <label for="password">Password</label>
        </div>
        <div>
            <input name="member_password" type="password"
                value="<?php if(isset($_COOKIE["member_password"])) { echo $_COOKIE["member_password"]; } ?>"
                class="input-field">
        </div>
    </div>
    <div class="field-group">
        <div>
            <input type="checkbox" name="remember" id="remember"
                <?php if(isset($_COOKIE["member_login"])) { ?> checked
                <?php } ?> /> <label for="remember-me">Remember me</label>
        </div>
    </div>
    <div class="field-group">
        <div>
            <input type="submit" name="login" value="Login"
                class="form-submit-button"></span>
        </div>
    </div>
</form>