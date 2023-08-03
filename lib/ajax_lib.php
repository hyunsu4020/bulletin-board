<?php
include_once $_SERVER["DOCUMENT_ROOT"]."/inc/dbcon.php";

Class Memos{

    public static function memoWrite($params){
        global $mysqli;

        $memo  = $params['memo'];
        $bid  = $params['bid'];
        $memoid = $params['memoid']??0;
        $file_table_id = $params['file_table_id'];

        $sql="INSERT INTO memo
        (bid, pid, userid, memo, status)
        VALUES(".$bid.", ".$memoid.", '".$_SESSION['UID']."', '".$memo."', 1)";
        $result=$mysqli->query($sql) or die($mysqli->error);
        if($result)$last_memoid = $mysqli -> insert_id;

        //메모 첨부 이미지 업데이트
        if($file_table_id){//첨부한 파일이 있는 경우에만
            $uq="update file_table_memo set bid=".$bid.", memoid=".$last_memoid." where fid=".$file_table_id;
            $ur=$mysqli->query($uq) or die($mysqli->error);

            $fquery="select * from file_table_memo where status=1 and fid=".$file_table_id;
            $file_result = $mysqli->query($fquery) or die("query error => ".$mysqli->error);
            $frs = $file_result->fetch_object();
            $img = "<img src='/data/".$frs->filename."' style='max-width:90%'>";
        }else{
            $img='';
        }

        return "<div class=\"card mb-4\" id=\"memo_".$last_memoid."\" style=\"max-width: 100%;margin-top:20px;\">
        <div class=\"row g-0\">
            <div class=\"col-md-12\">
            <div class=\"card-body\">
            <p class=\"card-text\">".$img."<br>".$memo."</p>
            <p class=\"card-text\"><small class=\"text-muted\">".$_SESSION['UID']." / now</small></p>
            <p class=\"card-text\" style=\"text-align:right\"><a href=\"javascript:;\" onclick=\"memo_modi(".$last_memoid.")\">수정</a> / <a href=\"javascript:;\" onclick=\"memo_del(".$last_memoid.")\">삭제</a></p>
            </div>
        </div>
        </div>
        </div>";
    }

    public static function memoDelete($params){
        global $mysqli;

        $memoid = $params['memoid'];

        $result = $mysqli->query("select * from memo where memoid=".$memoid) or die("query error => ".$mysqli->error);
        $rs = $result->fetch_object();

        if($rs->userid!=$_SESSION['UID']){
            $retun_data = array("result"=>"my");
            return json_encode($retun_data);
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
            return json_encode($retun_data);
        }else{
            $retun_data = array("result"=>"no");
            return json_encode($retun_data);
        }
    }


}


?>