<?php include $_SERVER["DOCUMENT_ROOT"]."/inc/dbcon.php";
ini_set( 'display_errors', '0' );
class Members {//클래스의 이름 보통 첫글자를 대문자로 사용, MemberClass 이런식으로 카멜표기법사용
    private $name;//클래스에서 사용하는 변수 선언, private은 클래스 안에서만 사용,
    protected $email;//protected는 확장 클래스에서도 사용

    public function __construct(){//생성자, 클래스를 선언할때 제일 먼저 작동, 아래 내용은 해당 변수에 값을 입력함.
        $this->name = "tester";
        $this->email = "t@t.com";
    }

    public function disp(){
        echo "name is ".$this->name;
        echo "<br>";
        echo "email is ".$this->email;
    }

    public function minfo($userid){
        global $mysqli;
        $query = "select username,email from members where userid='".$userid."'";
        $result = $mysqli->query($query) or die("query error => ".$mysqli->error);
        $rs = $result->fetch_object();
        $this->name = $rs->username;
        $this->email = $rs->email;
        return $this;
    }

    public static function minfo2($userid){
        global $mysqli;
        $query = "select username,email from members where userid='".$userid."'";
        $result = $mysqli->query($query) or die("query error => ".$mysqli->error);
        $rs = $result->fetch_object();
        $userinfo[0] = $rs->username;
        $userinfo[1] = $rs->email;
        return $userinfo;
    }
}
echo "<pre>";
$member = new Members();//Mebers클래스를 사용하기 위한 인스턴스 생성
$member->disp();//인스턴스를 이용해 Members클래스안에 있는 disp()함수를 호출. 현재 생성자에서 만든 변수값들을 출력
echo "<br>";
echo $member->minfo('admin')->disp();
echo "<br>";
$minfo=Members::minfo2('admin');
print_r($minfo);
echo "<br>";
$minfo2=$member->minfo2('admin');
print_r($minfo2);
echo "<br>";

class Users extends Members {

    private $regdate;
    public $userArray = array();

    public function disp2(){
        echo "이름은 ".$this->name;
        echo "<br>";
        echo "이메일은 ".$this->email;
    }

    public function disp3(){
        echo "가입일은 ".$this->regdate;
    }

    public function disp4($userid){
        echo "게시물등록갯수: ".self::minfo4($userid);
    }

    public function disp5($userid){
        $this->userArray=parent::minfo2($userid);
    }

    public function minfo3($userid){
        global $mysqli;
        $query = "select regdate from members where userid='".$userid."'";
        $result = $mysqli->query($query) or die("query error => ".$mysqli->error);
        $rs = $result->fetch_object();
        $this->regdate = $rs->regdate;
        return $this;
    }

    public static function minfo4($userid){
        global $mysqli;
        $query = "select count(*) as cnt from board where status=1 and userid='".$userid."'";
        $result = $mysqli->query($query) or die("query error => ".$mysqli->error);
        $rs = $result->fetch_object();
        return $rs->cnt;

    }
}

$user = new Users();
$user->disp();
echo "<br>";
echo $userinfo=$user->minfo('admin')->disp2();
echo "<br>";
$userinfo=Users::minfo2('admin');
print_r($userinfo);
echo "<br>";
echo $user->minfo3('admin')->disp3();
echo "<br>";
$boardcnt=Users::minfo4('admin');
echo "등록한 게시물수:".$boardcnt;
echo "<br>";
$user->disp4('admin');
echo "<br>";
$user->disp5('admin');
print_r($user->userArray);
echo "<br>";
?>