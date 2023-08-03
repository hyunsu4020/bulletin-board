<?php session_start();
include $_SERVER["DOCUMENT_ROOT"]."/inc/dbcon.php";
ini_set( 'display_errors', '0' );

if(!$_SESSION['UID']){
    $retun_data = array("result"=>"member");
    echo json_encode($retun_data);
    exit;
}

if($_FILES['savefile']['size']>10240000){//10메가
    $retun_data = array("result"=>"size");
    echo json_encode($retun_data);
    exit;
}

if($_FILES['savefile']['type']!='image/jpeg' and $_FILES['savefile']['type']!='image/gif' and $_FILES['savefile']['type']!='image/png'){//이미지가 아니면, 다른 type은 and로 추가
    $retun_data = array("result"=>"image");
    echo json_encode($retun_data);
    exit;
}

$save_dir = $_SERVER['DOCUMENT_ROOT']."/data/";//파일을 업로드할 디렉토리
$filename = $_FILES["savefile"]["name"];
$ext = pathinfo($filename,PATHINFO_EXTENSION);//확장자 구하기
$newfilename = date("YmdHis").substr(rand(),0,6);
$savefile = $newfilename.".".$ext;//새로운 파일이름과 확장자를 합친다

if(move_uploaded_file($_FILES["savefile"]["tmp_name"], $save_dir.$savefile)){//파일 등록에 성공하면 디비에 등록해준다.
    $sql="INSERT INTO php.file_table
    (userid, filename)
    VALUES('".$_SESSION['UID']."', '".$savefile."')";
    $result = $mysqli->query($sql) or die($mysqli->error);
    $fid = $mysqli -> insert_id;
    $retun_data = array("result"=>"success", "fid"=>$fid, "savename"=>$savefile);
    echo json_encode($retun_data);
    exit;
}else{
    $retun_data = array("result"=>"error");
    echo json_encode($retun_data);
    exit;
}


?>