<?php session_start();
include $_SERVER['DOCUMENT_ROOT']."/inc/dbcon.php";
ini_set( 'display_errors', '0' );

        if($_FILES['file']['size']>10240000){//10메가
            $retun_data = array("result"=>"fail", "msg"=>"이미지의 용량은 10메가까지만 등록할 수 있습니다.");
            echo json_encode($retun_data);
            exit;
        }
        $ext = substr(strrchr($_FILES['file']['name'],"."),1);
        $ext = strtolower($ext);
        if ($ext != "jpg" and $ext != "png" and $ext != "jpeg" and $ext != "gif")
        {
            $retun_data = array("result"=>"fail", "msg"=>"이미지파일만 등록할 수 있습니다.");
            echo json_encode($retun_data);
            exit;
        }

        $name = "summer_".date("YmdHis").substr(rand(),0,4);
        $filename = $name.'.'.$ext;
        $destination = $_SERVER['DOCUMENT_ROOT'].'/data/'.$filename;
        $location =  $_FILES["file"]["tmp_name"];
        if(move_uploaded_file($location,$destination)){//이미지를 등록하면 테이블에 저장한다.
            $sql="INSERT INTO php.file_table_summer
            (userid, filename)
            VALUES('".$_SESSION['UID']."', '".$filename."')";
            $result = $mysqli->query($sql) or die($mysqli->error);
            $fid = $mysqli -> insert_id;
        }else{
            $retun_data = array("result"=>"fail", "msg"=>"등록에 실패했습니다. 관리자에게 문의하십시오.");
            echo json_encode($retun_data);
            exit;
        }

        $retun_data = array("result"=>"ok",
                            "filename"=>$filename,
                            "fid"=>$fid
                        );
        echo json_encode($retun_data);


?>