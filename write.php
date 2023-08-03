<?php include $_SERVER["DOCUMENT_ROOT"]."/inc/header.php";

if(!$_SESSION['UID']){
    echo "<script>alert('회원 전용 게시판입니다.');history.back();</script>";
    exit;
}

$multi=$_GET['multi']??"free";//게시판 구분자
$bid=$_GET["bid"];//get으로 넘겼으니 get으로 받는다.
$parent_id=$_GET["parent_id"];

if($bid){//bid가 있다는건 수정이라는 의미다.

    $result = $mysqli->query("select * from board where bid=".$bid) or die("query error => ".$mysqli->error);
    $rs = $result->fetch_object();

    if($rs->userid!=$_SESSION['UID']){
        echo "<script>alert('본인 글이 아니면 수정할 수 없습니다.');history.back();</script>";
        exit;
    }

    $fquery="select * from file_table where status=1 and bid=".$rs->bid." order by fid asc";
    $file_result = $mysqli->query($fquery) or die("query error => ".$mysqli->error);
    while($frs = $file_result->fetch_object()){
        $fileArray[]=$frs;
    }

}

if($parent_id){//parent_id가 있다는건 답글이라는 의미다.

    $result = $mysqli->query("select * from board where bid=".$parent_id) or die("query error => ".$mysqli->error);
    $rs = $result->fetch_object();
    $rs->subject = "[RE]".$rs->subject;
}

    //기존에 삽입한 이미지가 있다면 정보를 불러온다.
    if($bid){
        $results = $mysqli->query("select GROUP_CONCAT(fid) as fids from file_table_summer where bid=".$bid) or die("query error => ".$mysqli->error);
        $fs = $results->fetch_object();
    }
?>
        <form method="post" action="write_ok.php" onsubmit="return sendform();" enctype="multipart/form-data">
            <input type="hidden" name="multi" value="<?php echo $multi;?>">    

            <input type="hidden" name="bid" value="<?php echo $bid;?>">
            <input type="hidden" name="parent_id" value="<?php echo $parent_id;?>">
            <input type="hidden" name="file_table_id" id="file_table_id" value="">
            <input type="hidden" name="summer_fid" id="summer_fid" value="<?php echo $fs->fids;?>">
            <div class="mb-3">
            <label for="exampleFormControlInput1" class="form-label">이름</label>
                <input type="text" name="username" class="form-control" id="exampleFormControlInput1" value="<?php echo member_name($_SESSION['UID']);?>">

            <label for="exampleFormControlInput1" class="form-label">제목</label>
                <input type="text" name="subject" class="form-control" id="exampleFormControlInput1" placeholder="제목을 입력하세요." value="<?php echo $rs->subject;?>">
            </div>
            <div class="mb-3">
            <label for="exampleFormControlTextarea1" class="form-label">내용</label>
            <!-- summernote 추가분 -->
            <div class="mb-3">
                <iframe id="summer" src="summernote.php?content=<?php echo urlencode(htmlspecialchars_decode($rs->content));?>" style="width:100%; height:520px; border:none" scrolling = "no"></iframe>
                <textarea id="content" name="content" style="display: none;"></textarea>
            </div>
            </div>
            <div class="mb-3">
                <input type="file" name="upfile[]" id="upfile" multiple class="form-control" aria-label="Large file input example">
            </div>
            <!-- 첨부된 이미지 표시 -->
            <div class="row row-cols-1 row-cols-md-6 g-4" id="imageArea">
                <?php
                    foreach($fileArray as $fa){
                ?>
                <div class="col" id="f_<?php echo $fa->fid;?>">
                    <div class="card h-100">
                        <img src="/data/<?php echo $fa->filename?>" class="card-img-top" >
                    <div class="card-body">
                        <button type="button" class="btn btn-warning" onclick="file_del(<?php echo $fa->fid;?>)">삭제</button>
                    </div>
                    </div>
                </div>
                <?php }?>
               
            </div>
            <!-- 첨부된 이미지 -->
            <br />
            <button type="submit" class="btn btn-primary">등록</button>
        </form>

<script>

    function sendform(){
        var contents=$('#summer').get(0).contentWindow.$('#summernote').summernote('code');//iframe에 있는 summernote함수를 작동시킨다.

        if ($('#summer').get(0).contentWindow.$('#summernote').summernote('isEmpty')) {
          alert('내용을 입력하세요.');
          return false;
        }

        $("#content").html(contents);

        return true;
    }

    $("#upfile").change(function(){

        var files = $('#upfile').prop('files');
        for(var i=0; i < files.length; i++) {
            attachFile(files[i]);
        }

        $('#upfile').val('');

    });  

    function attachFile(file) {
        var formData = new FormData();
        formData.append("savefile", file);
        $.ajax({
            url: 'save_image.php',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType : 'json' ,
            type: 'POST',
            success: function (return_data) {
                if(return_data.result=="member"){
                    alert('로그인 하십시오.');
                    return;
                }else if(return_data.result=="size"){
                    alert('10메가 이하만 첨부할 수 있습니다.');
                    return;
                }else if(return_data.result=="image"){
                    alert('이미지 파일만 첨부할 수 있습니다.');
                    return;
                }else if(return_data.result=="error"){
                    alert('첨부하지 못했습니다. 관리자에게 문의하십시오.');
                    return;
                }else{
                    fid = $("#file_table_id").val() + return_data.fid + ",";
                    $("#file_table_id").val(fid);
                    var html = "<div class='col' id='f_"+return_data.fid+"'><div class='card h-100'><img src='/data/"+return_data.savename+"' class='card-img-top'><div class='card-body'><button type='button' class='btn btn-warning' onclick='file_del("+return_data.fid+")'>삭제</button></div></div></div>";
                    $("#imageArea").append(html);
                }
            }
        });

    }



    function file_del(fid){

        if(!confirm('삭제하시겠습니까?')){
        return false;
        }
           
        var data = {
            fid : fid
        };
            $.ajax({
                async : false ,
                type : 'post' ,
                url : 'file_delete.php' ,
                data  : data ,
                dataType : 'json' ,
                error : function() {} ,
                success : function(return_data) {
                    if(return_data.result=="member"){
                        alert('로그인 하십시오.');
                        return;
                    }else if(return_data.result=="my"){
                        alert('본인이 작성한 글만 삭제할 수 있습니다.');
                        return;
                    }else if(return_data.result=="no"){
                        alert('삭제하지 못했습니다. 관리자에게 문의하십시오.');
                        return;
                    }else{
                        $("#f_"+fid).hide();
                    }
                }
        });

    }

</script>

<?php
include $_SERVER["DOCUMENT_ROOT"]."/inc/footer.php";
?>