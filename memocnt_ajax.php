<?php session_start();
include $_SERVER["DOCUMENT_ROOT"]."/inc/dbcon.php";
header('Content-Type: text/event-stream');//필수적으로 넣어준다.
header('Cache-Control: no-cache');//필수적으로 넣어준다.
$query="select count(*) from board b
        join memo m on b.bid=m.bid
        where b.userid='".$_SESSION['UID']."' and m.status=1 and m.isread=0";
$memo_cnt = $mysqli->query($query) or die("query error => ".$mysqli->error);
$rs = $memo_cnt->fetch_array();
$cnt = $rs[0];    

// retry와 data 뿌려줌
// retry뒤에 있는 숫자가 리로딩 되는 시간이다. 1000이면 1초다
echo "retry:1000\ndata:".$cnt."\n\n";

flush();

?>