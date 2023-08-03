<?php session_start();
include $_SERVER["DOCUMENT_ROOT"]."/lib/ajax_lib.php";

if(!$_SESSION['UID']){
    echo "member";
    exit;
}

$method=$_POST['method'];
$params=$_REQUEST['params'];

echo $method($params);

?>