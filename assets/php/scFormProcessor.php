<?php
error_reporting(E_ALL);
define('__ROOT__', dirname(dirname(__FILE__)));
require_once (__ROOT__.'/lib/ft-nonce-lib.php');

function contains_bad_str($str_to_test) {
    $bad_strings = array(
        "content-type:"
        ,"mime-version:"
        ,"multipart/mixed"
        ,"Content-Transfer-Encoding:"
        ,"bcc:"
        ,"cc:"
        ,"to:"
    );
    foreach($bad_strings as $bad_string) {
        if(@preg_match($bad_string.'/i', strtolower($str_to_test))) {
            return true;
        }
    }
    return false;
}

function contains_newlines($str_to_test) {
    return @preg_match("/(%0A|%0D|\\n+|\\r+)/i", $str_to_test);
}

function reply($code, $isError = TRUE){
    $response = ($isError)?"error_$code":$code;
    die($response);
}

if ($_POST['simply_contact_form_page'] != "simply-contact/simply-contact.php") reply("5");
if (!ft_nonce_is_valid($_POST['simply_contact_form_nonce'], $_POST['simply_contact_form_action'], 'simply_contact')) reply("6");
$from = trim($_POST['simply_contact_form_from']);
$subject = trim($_POST['simply_contact_form_subject']);
$message = trim( $_POST['simply_contact_form_message']);

if($from==null || $from == "" || contains_bad_str($from) || contains_newlines($from))  reply("0");
if($subject==null || $subject == "" || contains_bad_str($subject) || contains_newlines($subject)) reply("2");
if($message==null || $message == "" || contains_bad_str($message)) reply("3");

$headers = sprintf('From: '.$from. "\r\n");

require( '../../../../../wp-load.php' );
$to = get_option('simplyContact_email');

add_filter('wp_mail_content_type',create_function('', 'return "text";'));
if(!wp_mail( $to, $subject, $message, $headers)) reply("4");

reply("ok",false);

?>
