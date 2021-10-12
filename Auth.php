<?php
require "DBController.php";
class Auth {
    function getMemberByUsername($username) {
        $db_handle = new DBController();
        $query = "Select * from members where member_name = ?";
        $result = $db_handle->runQuery($query, 's', array($username));
        return $result;
    }
    
	function getTokenByUsername($username,$expired) {
	    $db_handle = new DBController();
	    $query = "Select * from tbl_token_auth where username = ? and is_expired = ?";
	    $result = $db_handle->runQuery($query, 'si', array($username, $expired));
	    return $result;
    }

    function getAuthTokenByUserid($userid) {
        $db_handle = new DBController();
        $query = "Select * from auth_tokens where userid = ?";
        $result = $db_handle->runQuery($query, 'i', array($userid));
        return $result;
    }
        

    function getAuthTokenBySelector($selector) {
        $db_handle = new DBController();
        $query = "Select * from auth_tokens where selector = ?";
        $result = $db_handle->runQuery($query, 's', array($selector));
        return $result;
    }
        
    function markAsExpired($tokenId) {
        $db_handle = new DBController();
        $query = "UPDATE tbl_token_auth SET is_expired = ? WHERE id = ?";
        $expired = 1;
        $result = $db_handle->update($query, 'ii', array($expired, $tokenId));
        return $result;
    }
    
    function insertToken($username, $random_password_hash, $random_selector_hash, $expiry_date) {
        $db_handle = new DBController();
        $query = "INSERT INTO tbl_token_auth (username, password_hash, selector_hash, expiry_date) values (?, ?, ?, ?)";
        $result = $db_handle->insert($query, 'ssss', array($username, $random_password_hash, $random_selector_hash, $expiry_date));
        return $result;
    }
    
    function insertAuthToken($userid,$hashedValidator,$selector,$expires){
    $db_handle = new DBController();
    $query = "INSERT INTO auth_tokens (userid,hashedValidator,selector,expires) values (?, ?, ?,?)";
    $result = $db_handle->insert($query,'isss',array($userid,$hashedValidator,$selector,$expires));
    return $result;
    }

    function deleteAuthToken($userid) {
        $db_handle = new DBController();
        $query = "DELETE FROM auth_tokens WHERE userid = ?";
        $result = $db_handle->update($query, 'i', array($userid));
        return $result;
        }
   }
?>