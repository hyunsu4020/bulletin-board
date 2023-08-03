<?php
include $_SERVER["DOCUMENT_ROOT"]."/inc/dbcon.php";
ini_set( 'display_errors', '0' );
$search_keyword = $_POST['search_keyword'];

if($search_keyword){
    $search_where = " and (subject like '%".$search_keyword."%' or content like '%".$search_keyword."%')";
}

$pageNumber  = $_POST['pageNumber']??1;//현재 페이지, 없으면 1
if($pageNumber < 1) $pageNumber = 1;
$pageCount  = $_POST['pageCount']??10;//페이지당 몇개씩 보여줄지, 없으면 10
$startLimit = ($pageNumber-1)*$pageCount;//쿼리의 limit 시작 부분
$totalCount = $_POST['totalCount'];

$sql = "select * from board where 1=1";
$sql .= " and status=1";
$sql .= $search_where;
$order = " order by ifnull(parent_id, bid) desc, bid asc";
$limit = " limit $startLimit, $pageCount";
$query = $sql.$order.$limit;
$result = $mysqli->query($query) or die("query error => ".$mysqli->error);
while($rs = $result->fetch_object()){
    $rsc[]=$rs;
}

    $data="";
    $idNumber = $totalCount - ($pageNumber-1)*$pageCount;
    foreach($rsc as $r){
        //검색어만 하이라이트 해준다.
        $subject = str_replace($search_keyword,"<span style='color:red;'>".$search_keyword."</
            span>",$r->subject);

        $data.="
            <tr>
                <th scope=\"row\">".$idNumber."</th>
                <td>".$r->userid."</td>
                <td>";
       
        if($r->parent_id){
            $data.="&nbsp;&nbsp;";
        }

        $data.="<a href=\"/view.php?bid=".$r->bid."\">".$subject."</a></td>
                <td>".$r->regdate."</td>
            </tr>";
        $idNumber--;
    }

    if($data){
        echo $data;
    }else{
        echo false;
    }
?>