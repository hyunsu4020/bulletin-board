<?php
include $_SERVER["DOCUMENT_ROOT"]."/inc/header.php";

$search_keyword = $_GET['search_keyword'];

if($search_keyword){
    $search_where = " and (subject like '%".$search_keyword."%' or content like '%".$search_keyword."%')";
}

$pageNumber  = $_GET['pageNumber']??1;//현재 페이지, 없으면 1
if($pageNumber < 1) $pageNumber = 1;
$pageCount  = $_GET['pageCount']??10;//페이지당 몇개씩 보여줄지, 없으면 10
$firstPageNumber  = $_GET['firstPageNumber'];
if($firstPageNumber < 1) $firstPageNumber = 1;

    $multi=$_GET['multi']??"free";//게시판 구분자
    $boards = new Boards($multi);//인스턴스를 생성한다
    $rsc=$boards->boardLists($multi, $search_keyword, $pageNumber, $pageCount);//클래스에 있는 함수를 이용해 게시물 리스트를 가져온다.
//    print_r($rsc);
?>
        <div><?php //클래스 적용부분
            $bi=$boards->boardInfo();
            echo $bi["boardName"]." ( 총 게시물수 : ".$bi["boardCount"]." / 마지막등록일 : ".$bi["boardLastDate"].")";
        ?></div>
        <?php
            if($_SESSION['UID']){//로그인 한 경우만 댓글 여부를 알려준다.
        ?>
            <div style="text-align:right;font-weight:600;">댓글 : <span id="memocnt">0<span>개</div>
        <?php }?>
        <!-- 더보기 버튼을 클릭하면 다음 페이지를 넘겨주기 위해 현재 페이지에 1을 더한 값을 준비한다. 더보기를 클릭할때마다 1씩 더해준다. -->
        <input type="hidden" name="nextPageNumber" id="nextPageNumber" value="<?php echo $pageNumber+1;?>">
        <table class="table">
        <thead>
            <tr>
            <th scope="col">번호</th>
            <th scope="col">글쓴이</th>
            <th scope="col">썸네일</th>
            <th scope="col">제목</th>
            <th scope="col">등록일</th>
            </tr>
        </thead>
        <tbody id="board_list">
            <?php
                $totalCount = Boards::totalCount($multi,$search_keyword);//전체 게시물 수를 가져오는 클래스
                $idNumber = $totalCount - ($pageNumber-1)*$pageCount;
                foreach($rsc as $r){
                    //검색어만 하이라이트 해준다.
                    $subject = str_replace($search_keyword,"<span style='color:red;'>".$search_keyword."</
                        span>",$r->subject);
                   
            ?>

                <tr>
                    <th scope="row"><?php echo $idNumber--;?></th>
                    <td><?php echo member_name($r->userid);?></td>
                    <td><?php
                        if(!empty($r->thumb)){
                            echo "<img src='/data/".$r->thumb."' width='50'>";
                        }else{
                            echo "null";
                        }
                    ?></td>
                    <td>
                        <?php
                            if($r->parent_id){
                                echo "&nbsp;&nbsp;<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-arrow-return-right\" viewBox=\"0 0 16 16\">
                                <path fill-rule=\"evenodd\" d=\"M1.5 1.5A.5.5 0 0 0 1 2v4.8a2.5 2.5 0 0 0 2.5 2.5h9.793l-3.347 3.346a.5.5 0 0 0 .708.708l4.2-4.2a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 8.3H3.5A1.5 1.5 0 0 1 2 6.8V2a.5.5 0 0 0-.5-.5z\"/>
                              </svg>";
                            }
                        ?>  
                    <a href="/view.php?bid=<?php echo $r->bid;?>"><?php echo $subject?></a>
                    <?php if($r->filecnt){?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-image" viewBox="0 0 16 16">
                        <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>

                        <path d="M1.5 2A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13zm13 1a.5.5 0 0 1 .5.5v6l-3.775-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12v.54A.505.505 0 0 1 1 12.5v-9a.5.5 0 0 1 .5-.5h13z"/>
                        </svg>
                    <?php }?>
                    <?php if($r->memocnt){?>
                        <span <?php if((time()-strtotime($r->memodate))<=86400){ echo "style='color:red;'";}?>>
                            [<?php echo $r->memocnt;?>]
                        </span>
                    <?php }?>
                    <?php if($r->newid){?>
                        <span class="badge bg-danger">New</span>
                    <?php }?>
                </td>
                    <td><?php echo $r->regdate?></td>
                </tr>
            <?php }?>
        </tbody>
        </table>
        <!-- <div class="d-grid gap-2" style="margin:20px;">
            <button class="btn btn-secondary" type="button" id="more_button">더보기</button>
        </div> -->
        <form method="get" action="<?php echo $_SERVER["PHP_SELF"]?>">
        <div class="input-group mb-12" style="margin:auto;width:50%;">

                <input type="text" class="form-control" name="search_keyword" id="search_keyword" placeholder="제목과 내용에서 검색합니다." value="<?php echo $search_keyword;?>" aria-label="Recipient's username" aria-describedby="button-addon2">

                <button class="btn btn-outline-secondary" type="button" id="search">검색</button>
        </div>
        </form>
        <p>
            <?php echo Boards::paging($multi, $search_keyword, $pageNumber, $pageCount, $firstPageNumber);?>
        </p>

        <p style="text-align:right;">

            <?php
                if($_SESSION['UID']){
            ?>
                <a href="write.php?multi=<?php echo $multi;?>"><button type="button" class="btn btn-primary">등록</button><a>
                <a href="/member/logout.php"><button type="button" class="btn btn-primary">로그아웃</button><a>
            <?php
                }else{
            ?>
                <a href="/member/login.php"><button type="button" class="btn btn-primary">로그인</button><a>
                <a href="/member/signup.php"><button type="button" class="btn btn-primary">회원가입</button><a>
            <?php
                }
            ?>
        </p>

<script>
<?php
    if($_SESSION['UID']){//로그인 한 경우만 작동한다.
?>
    if(typeof(EventSource) !== "undefined") {//sse가 가능한지 확인
        var source = new EventSource("memocnt_ajax.php");//sse를 통해 파일을 읽어온다 get방식으로 변수를 넘길수도 있다.
        source.onmessage = function(event) {    
            $("#memocnt").text(event.data);//ajax파일에서 넘겨준 값을 표시한다.
        };
    }
<?php }?>
   
</script>

<?php
include $_SERVER["DOCUMENT_ROOT"]."/inc/footer.php";