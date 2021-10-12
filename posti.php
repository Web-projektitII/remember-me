<?php
require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

define('SMTPUSERNAME',$smtpUsername);
define('SMTPPASSWORD',$smtpPassword);

function posti($emailTo,$msg,$subject){
$emailFrom = "wohjelmointi@gmail.com";
$emailFromName = "Ohjelmointikurssi";
$emailToName = "";
$mail = new PHPMailer;
$mail->isSMTP(); 
$mail->CharSet = 'UTF-8';
$mail->SMTPDebug = 0; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
$mail->Host = "smtp.gmail.com"; // use $mail->Host = gethostbyname('smtp.gmail.com'); // if your network does not support SMTP over IPv6
$mail->Port = 587; // TLS only
$mail->SMTPSecure = 'tls'; // ssl is depracated
$mail->SMTPAuth = true;
$mail->Username = SMTPUSERNAME;
$mail->Password = SMTPPASSWORD;
$mail->setFrom($emailFrom, $emailFromName);
$mail->addAddress($emailTo, $emailToName);
$mail->Subject = $subject;
$mail->msgHTML($msg); //$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
$mail->AltBody = 'HTML messaging not supported';
// $mail->addAttachment('images/phpmailer_mini.png'); //Attach an image file
if(!@$mail->send()){
    $tulos = false;
    $errorMsg = "Postin virhe: " .$mail->ErrorInfo. " osoitteeseen $emailTo\n";
    $errorMsg.= SMTPUSERNAME.":".SMTPPASSWORD;
    debuggeri($errorMsg);
}else{
    $tulos = true;
    debuggeri("Viesti lähetetty: $emailTo!");
}
return $tulos;
}
?>