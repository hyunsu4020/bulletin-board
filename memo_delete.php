<?php session_start();
include $_SERVER["DOCUMENT_ROOT"]."/inc/dbcon.php";
ini_set( 'display_errors', '0' );

if(!$_SESSION['UID']){
    $retun_data = array("result"=>"member");
    echo json_encode($retun_data);
    exit;
}

$memoid = $_POST['memoid'];

$result = $mysqli->query("select * from memo where memoid=".$memoid) or die("query error => ".$mysqli->error);
$rs = $result->fetch_object();

if($rs->userid!=$_SESSION['UID']){
    $retun_data = array("result"=>"my");
    echo json_encode($retun_data);
    exit;
}

$sql="update memo set status=0 where memoid=".$memoid;//status값을 바꿔준다.
$result=$mysqli->query($sql) or die($mysqli->error);
if($result){
   
    $fquery="select * from file_table_memo where status=1 and memoid=".$memoid;
    $file_result = $mysqli->query($fquery) or die("query error => ".$mysqli->error);
    $frs = $file_result->fetch_object();
    if($frs->filename){//첨부한 파일이 있는 경우에만 삭제처리
        $delete_file=$_SERVER["DOCUMENT_ROOT"]."/data/".$frs->filename;
        if(unlink($delete_file)){
            $sql2="update file_table_memo set status=0 where fid=".$frs->fid;//status값을 바꿔준다.
            $result2=$mysqli->query($sql2) or die($mysqli->error);
        }
    }

    $retun_data = array("result"=>"ok");
    echo json_encode($retun_data);
}else{
    $retun_data = array("result"=>"no");
    echo json_encode($retun_data);
}

?>