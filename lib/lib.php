<?php session_start();
include $_SERVER["DOCUMENT_ROOT"]."/inc/dbcon.php";

//글 작성자의 이름을 가져오기 위해 함수를 사용했다. 매개변수인 userid를 통해 이름을 알아낸 후 그 값을 리턴해 준다.
function member_name($userid){
    global $mysqli;//$mysqli 는 dbconn.php에서 만들어진 변수이기때문에 함수안에서 사용하려면 global로 선언해주어야한다.
    $query = "select username from members where userid='".$userid."'";

    $result = $mysqli->query($query) or die("query error => ".$mysqli->error);
    $rs = $result->fetch_object();
    return $rs->username;
}

class Boards {
    private $boardName;//게시판명
    private $boardCount;//게시물수
    private $boardLastDate;//마지막으로 게시물이 등록된 날짜

    public function boardname($multi){
        switch($multi) {
            case "free":$rs="자유게시판";
            break;
            case "humor":$rs="유머게시판";
            break;
            case "star":$rs="연예인게시판";
            break;
        }
        return $rs;
    }

    public function __construct($multi="free"){//생성자다. 생성자에 파라미터가 있다. 파라미터값이 없으면 "free"로 지정한다.
        global $mysqli;
        $query = "select regdate, cnt from board b1
        join (
                select b2.multi, count(*) as cnt from board b2 where status=1 group by b2.multi
        ) board2 on b1.multi=board2.multi
        where b1.multi='".$multi."'
        and status=1 order by bid desc limit 1;";
        $result = $mysqli->query($query) or die("query error => ".$mysqli->error);
        $rs = $result->fetch_object();
        $this->boardName=self::boardname($multi);
        $this->boardCount=$rs->cnt;
        $this->boardLastDate=$rs->regdate;
    }

    public function boardInfo(){
        $bi = array(
            "boardName"=>$this->boardName,
            "boardCount"=>$this->boardCount,
            "boardLastDate"=>$this->boardLastDate
        );
        return $bi;//게시판 정보를 배열로 만들어서 리턴한다.
    }

    public function boardLists($multi="free", $search_keyword = null, $pageNumber = 1, $pageCount = 10){
        global $mysqli;
        $sql = "select b.*, if((now() - regdate)<=86400,1,0) as newid
        ,(select count(*) from memo m where m.status=1 and m.bid=b.bid) as memocnt
        ,(select m.regdate from memo m where m.status=1 and m.bid=b.bid order by m.memoid desc limit 1) as memodate
        ,(select count(*) from file_table f where f.status=1 and f.bid=b.bid) as filecnt
        ,(select filename from file_table_summer fs where fs.status=1 and fs.bid=b.bid order by fs.fid asc limit 1) as thumb
        from board b where multi='".$multi."' ";
        $sql .= " and status=1";
        if(!empty($search_keyword)){
            $search_where = " and (subject like '%".$search_keyword."%' or content like '%".$search_keyword."%')";
        }
        $sql .= $search_where;
        $order = " order by ifnull(parent_id, bid) desc, bid asc";
        if($pageNumber < 1) $pageNumber = 1;
        $startLimit = ($pageNumber-1)*$pageCount;//쿼리의 limit 시작 부분
        $limit = " limit $startLimit, $pageCount";

        $query = $sql.$order.$limit;
        $result = $mysqli->query($query) or die("query error => ".$mysqli->error);
        while($rs = $result->fetch_object()){
            $rsc[]=$rs;
        }
       
        return $rsc;
    }

    public static function totalCount($multi="free", $search_keyword = null){//전체게시물 수 구하기
        global $mysqli;
        $sqlcnt = "select count(*) as cnt from board where multi='".$multi."' ";
        $sqlcnt .= " and status=1";
        if(!empty($search_keyword)){
            $search_where = " and (subject like '%".$search_keyword."%' or content like '%".$search_keyword."%')";
        }
        $sqlcnt .= $search_where;
        $countresult = $mysqli->query($sqlcnt) or die("query error => ".$mysqli->error);
        $rscnt = $countresult->fetch_object();
        $totalCount = $rscnt->cnt;
        return $totalCount;
    }

    public static function paging($multi="free", $search_keyword = null, $pageNumber = 1, $pageCount = 10, $firstPageNumber = 1){
        $totalCount=self::totalCount($multi, $search_keyword);//전체 게시물 수를 구하는 클래스
        $totalPage = ceil($totalCount/$pageCount);//전체 페이지를 구한다.
        $lastPageNumber = $firstPageNumber + $pageCount - 1;//페이징 나오는 부분에서 레인지를 정한다.
        if($lastPageNumber > $totalPage) $lastPageNumber = $totalPage;        

        $paging="
            <nav aria-label=\"Page navigation example\">
                <ul class=\"pagination justify-content-center\">
                    <li class=\"page-item\">
        ";
        $pageNumber1=$firstPageNumber-$pageCount;
        $paging.="
            <a class=\"page-link\" href=\"".$_SERVER['PHP_SELF']."?pageNumber=".$pageNumber1."&firstPageNumber=".$pageNumber1."&search_keyword=".$search_keyword."&multi=".$multi."\">Previous</a>";
        $paging.="</li>";
        for($i=$firstPageNumber;$i<=$lastPageNumber;$i++){
            $paging.='
                <li class="page-item ';
            if($pageNumber==$i){
                $paging.='active';
            }
            $paging.='
                "><a class="page-link" href="'.$_SERVER['PHP_SELF'].'?pageNumber='.$i.'&firstPageNumber='. $firstPageNumber.'&search_keyword='.$search_keyword.'&multi='.$multi.'">'.$i.'</a></li>
            ';
        }
        $pageNumber2=$firstPageNumber+$pageCount;
        $paging.='
            <li class="page-item">
                    <a class="page-link" href="'.$_SERVER['PHP_SELF'].'?pageNumber='.$pageNumber2.'&firstPageNumber='.$pageNumber2.'&search_keyword='.$search_keyword.'&multi='.$multi.'">Next</a>
                </li>
            </ul>
            </nav>
        ';

        //error_log ('['.__FILE__.']['.__FUNCTION__.']['.__LINE__.']['.date("YmdHis").']'.print_r($paging,true)."\n", 3, './php_log_'.date("Ymd").'.log');//로그를 남긴다.

        return $paging;

    }

    public static function boardWrite($multi="free", $bid = null, $parent_id = null, $subject, $content){//게시물 등록
        global $mysqli;
        $userid=$_SESSION['UID'];
        if($bid){//bid값이 있으면 수정이고 아니면 등록이다.
            $result = $mysqli->query("select * from board where bid=".$bid) or die("query error => ".$mysqli->error);
            $rs = $result->fetch_object();
            if($rs->userid!=$_SESSION['UID']){
                return -1;
                exit;
            }
            $sql="update board set subject='".$subject."', content='".$content."', modifydate=now() where bid=".$bid;
        }else{
            if($parent_id){//답글인 경우 쿼리를 수정해서 parent_id를 넣어준다.
                $sql="insert into board (userid,subject,content,parent_id,multi) values ('".$userid."','".$subject."','".$content."',".$parent_id.",'".$multi."')";
            }else{
                $sql="insert into board (userid,subject,content,multi) values ('".$userid."','".$subject."','".$content."','".$multi."')";
            }
        }
        $result=$mysqli->query($sql) or die($mysqli->error);
        if(!$bid)$bid = $mysqli -> insert_id;
        return $bid;
    }

   
}

?>