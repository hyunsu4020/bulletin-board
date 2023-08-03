<?php session_start();
include $_SERVER["DOCUMENT_ROOT"]."/inc/dbcon.php";

if(!$_SESSION['UID']){
    echo "<script>alert('회원 전용 게시판입니다.');location.href='/index.php';</script>";
    exit;
}

$bid=$_POST["bid"]??$_GET["bid"];

if($bid){
    $result = $mysqli->query("select * from board where bid=".$bid) or die("query error => ".$mysqli->error);
    $rs = $result->fetch_object();

    if($rs->userid!=$_SESSION['UID']){
        echo "<script>alert('본인 글이 아니면 삭제할 수 없습니다.');location.href='/';</script>";
        exit;
    }

    $sql="update board set status=0 where bid=".$bid;//status값을 바꿔준다.
    $result=$mysqli->query($sql) or die($mysqli->error);

    //게시물에 첨부된 파일이 있으면 디비에서 조회 후 모두 삭제해준다.
    $file_result = $mysqli->query("select * from file_table where status=1 and bid=".$bid) or die("query error => ".$mysqli->error);
    while($rs = $file_result->fetch_object()){
        $delete_file=$_SERVER["DOCUMENT_ROOT"]."/data/".$rs->filename;
        unlink($delete_file);
    }

}else{
    echo "<script>alert('삭제할 수 없습니다.');history.back();</script>";
    exit;
}


if($result){
    echo "<script>alert('삭제했습니다.');location.href='/index.php';</script>";
    exit;
}else{
    echo "<script>alert('글삭제에 실패했습니다.');history.back();</script>";
    exit;
}


?>