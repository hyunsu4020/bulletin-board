<?php session_start();
include $_SERVER["DOCUMENT_ROOT"]."/inc/dbcon.php";
include_once $_SERVER["DOCUMENT_ROOT"]."/lib/lib.php";//클래스를 불러오기 위해 include
ini_set( 'display_errors', '0' );
if(!$_SESSION['UID']){
    echo "<script>alert('회원 전용 게시판입니다.');location.href='/index.php';</script>";
    exit;
}

$subject=$_POST["subject"];
$content=$_POST["content"];
$multi=$_POST["multi"];
$bid=$_POST["bid"];//bid값이 있으면 수정이고 아니면 등록이다.
$parent_id=$_POST["parent_id"];//parent_id가 있으면 답글이다.
$userid=$_SESSION['UID'];//userid는 세션값으로 넣어준다.
$status=1;//status는 1이면 true, 0이면 false이다.
$file_table_id=$_POST["file_table_id"];
$summer_fid=rtrim($_POST["summer_fid"],',');

$bid=Boards::boardWrite($multi, $bid, $parent_id, $subject, $content);//글 저장하는 클래스
if($bid==-1){
    echo "<script>alert('본인 글이 아니면 수정할 수 없습니다.');location.href='/';</script>";
    exit;
}

if($_FILES["upfile"]["name"][0]){//첨부한 파일이 있으면

    for($k=0;$k<count($_FILES["upfile"]["name"]);$k++){

        if($_FILES['upfile']['size'][$k]>10240000){//10메가
            echo "<script>alert('10메가 이하만 첨부할 수 있습니다.');history.back();</script>";
            exit;
        }

        if($_FILES['upfile']['type'][$k]!='image/jpeg' and $_FILES['upfile']['type'][$k]!='image/gif' and $_FILES['upfile']['type'][$k]!='image/png'){//이미지가 아니면, 다른 type은 and로 추가
            echo "<script>alert('이미지만 첨부할 수 있습니다.');history.back();</script>";
            exit;
        }

        $save_dir = $_SERVER['DOCUMENT_ROOT']."/data/";//파일을 업로드할 디렉토리
        $filename = $_FILES["upfile"]["name"][$k];
        $ext = pathinfo($filename,PATHINFO_EXTENSION);//확장자 구하기
        $newfilename = date("YmdHis").substr(rand(),0,6);
        $upfile = $newfilename.".".$ext;//새로운 파일이름과 확장자를 합친다
       
        if(move_uploaded_file($_FILES["upfile"]["tmp_name"][$k], $save_dir.$upfile)){//파일 등록에 성공하면 디비에 등록해준다.
            $sql="INSERT INTO php.file_table
            (bid, userid, filename)
            VALUES(".$bid.", '".$_SESSION['UID']."', '".$upfile."')";
            $result=$mysqli->query($sql) or die($mysqli->error);
        }

    }

}

if($file_table_id){
    $fid=explode(",",$file_table_id);
    foreach($fid as $f){
        if($f){
            $uq="update file_table set bid=".$bid." where fid=".$f;
            $ur=$mysqli->query($uq) or die($mysqli->error);
        }
    }
}

if($summer_fid){
    $results = $mysqli->query("select * from file_table_summer where fid in (".$summer_fid.")") or die("query error => ".$mysqli->error);
    while($fs = $results->fetch_object()){
        if(strpos($content,$fs->filename)){//등록한 글에 삽입한 이미지가 남아 있으면 테이블 정보 업데이트
            $uq="update file_table_summer set bid=".$bid." where fid=".$fs->fid;
            $ur=$mysqli->query($uq) or die($mysqli->error);
        }else{//그렇지 않으면 파일을 지우고 테이블의 정보도 지운다.
            $delete_file=$_SERVER["DOCUMENT_ROOT"]."/data/".$fs->filename;
            unlink($delete_file);
            $dq="delete from file_table_summer where fid=".$fs->fid;
            $dr=$mysqli->query($dq) or die($mysqli->error);
        }
    }
}


if($bid){
    echo "<script>location.href='/index.php';</script>";
    exit;
}else{
    echo "<script>alert('글등록에 실패했습니다.');history.back();</script>";
    exit;
}


?>