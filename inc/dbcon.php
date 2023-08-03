<?php

ini_set( 'display_errors', '0' );   // DB 연결이 안되면 '0'값을 '1'로 바꾸어 오류 메세지를 확인할 수 있다.
$hostname="localhost";
$dbuserid="root";
$dbpasswd="root";
$dbname="php";


$mysqli = new mysqli($hostname, $dbuserid, $dbpasswd, $dbname);
if ($mysqli->connect_errno) {
    die('Connect Error: '.$mysqli->connect_error);
}

?>