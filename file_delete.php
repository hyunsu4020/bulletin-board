<?php session_start();
include $_SERVER["DOCUMENT_ROOT"]."/inc/dbcon.php";
ini_set( 'display_errors', '0' );

if(!$_SESSION['UID']){
    $retun_data = array("result"=>"member");
    echo json_encode($retun_data);
    exit;
}

$fid = $_POST['fid'];

$result = $mysqli->query("select * from file_table where  fid=".$fid) or die("query error => ".$mysqli->error);
$rs = $result->fetch_object();

if($rs->userid!=$_SESSION['UID']){
    $retun_data = array("result"=>"my");
    echo json_encode($retun_data);
    exit;
}

$sql="update file_table set status=0 where fid=".$fid;//status값을 바꿔준다.
$result=$mysqli->query($sql) or die($mysqli->error);
if($result){
   
    //서버에 저장되어 있는 파일 삭제
    $delete_file=$_SERVER["DOCUMENT_ROOT"]."/data/".$rs->filename;
    unlink($delete_file);

    $retun_data = array("result"=>"ok");
    echo json_encode($retun_data);
}else{
    $retun_data = array("result"=>"no");
    echo json_encode($retun_data);
}

?>