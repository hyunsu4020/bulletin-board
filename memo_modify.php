<?php session_start();
include $_SERVER["DOCUMENT_ROOT"]."/inc/dbcon.php";
ini_set( 'display_errors', '0' );

if(!$_SESSION['UID']){
    echo "member";
    exit;
}

$memoid = $_POST['memoid'];

$result = $mysqli->query("select * from memo where memoid=".$memoid) or die("query error => ".$mysqli->error);
$rs = $result->fetch_object();

if($rs->userid!=$_SESSION['UID']){
    echo "my";
    exit;
}


echo "<form class=\"row g-3\">
  <div class=\"col-md-10\">
    <textarea class=\"form-control\" id=\"memo_text_".$rs->memoid."\" style=\"height: 60px\">".$rs->memo."</textarea>
  </div>
  <div class=\"col-md-2\">
    <button type=\"button\" class=\"btn btn-secondary\" onclick=\"memo_modify(".$rs->memoid.")\" >댓글수정</button>
  </div>
</form>";

?>