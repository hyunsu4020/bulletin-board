<?php
$content = urldecode($_GET['content']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Summernote</title>
 
</head>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<body>
 
  <div id="summernote"><?php echo $content;?></div>
  <script>
    //summernote 추가분
    $(document).ready(function() {
        var $summernote = $('#summernote').summernote({
            codeviewFilter: false,
            codeviewIframeFilter: true,
            lang: 'ko-KR',
            height: 450,
            callbacks: {
                onImageUpload: function (files) {
                    if(files.length>5){
                        alert('5개까지만 등록할 수 있습니다.');
                        return;
                    }
                    for(var i=0; i < files.length; i++) {
                        sendFile($summernote, files[i]);
                    }
                   
                }
            }
        });
    });

    function sendFile($summernote, file) {
        var formData = new FormData();
        formData.append("file", file);
        $.ajax({
            url: 'summerimagesave.php',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType : 'json' ,
            type: 'POST',
            success: function (data) {
                if(data.result=="fail"){
                    alert(data.msg);
                    return;
                }else{
                    filename="/data/"+data.filename;
                    $('#summernote').summernote('insertImage', filename, function ($image) {
                        $image.css('width', '90%');
                        $image.css('padding', '10px');
                    });
                    var fidval=data.fid+","+parent.$("#summer_fid").val()//부모창의 id값을 호출
                    parent.$("#summer_fid").val(fidval);
                }
            }
        });

    }
  </script>
</body>
</html>