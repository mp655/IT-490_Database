#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


function auth($name, $password) {
    ( $db = mysqli_connect ( 'localhost', 'root', '12345678', 'user_info' ) );
    if (mysqli_connect_errno())
    {
      $txt = "" .date("y-m-d") . " " . date("h:i:sa") . " " . "Faild to connect to MYSQL\r\n------------------------------------------------------------- \r\n \r\n";
      $file = file_put_contents('log.txt', $txt, FILE_APPEND | LOCK_EX);
      echo"Failed to connect to MYSQL<br><br> ". mysqli_connect_error();
      exit();
    }
    $save = "Successfully connected to MySQL<br><br>";
      $txt = "" .date("y-m-d") . " " . date("h:i:sa") . " " . "Successfully Connected to MYSQL\r\n";
      $file = file_put_contents('log.txt', $txt, FILE_APPEND | LOCK_EX);
	echo $save;
    mysqli_select_db($db, 'user_info' );
    $s = "select * from user where name = '$name' and password = SHA1('$password')";
    //echo "The SQL statement is $s";
    ($t = mysqli_query ($db,$s)) or die(mysqli_error());
    //$name = mysqli_real_escape_string ($name);
    $num = mysqli_num_rows($t);
    if ($num == 0){
      
      $txt = "" .date("y-m-d") . " " . date("h:i:sa") . " " . " $name: wrong username or password \r\n ------------------------------------------------------------- \r\n \r\n";
      $file = file_put_contents('log.txt', $txt, FILE_APPEND | LOCK_EX); 
      return false;
    }else
    {
      
      $txt = "" .date("y-m-d") . " " . date("h:i:sa") . " " . " $name: signed in \r\n ------------------------------------------------------------- \r\n \r\n";
      $file = file_put_contents('log.txt', $txt, FILE_APPEND | LOCK_EX);
      print "<br>Authorized";
      return true;
    }
}




function register($name,$password,$email) {
    ( $db = mysqli_connect ( 'localhost', 'root', '12345678', 'user_info' ) );
    if (mysqli_connect_errno())
    {
     //$txt = "" .date("y-m-d") . " " . date("h:i:sa") . " " . "Faild to connect to MYSQL\r\n------------------------------------------------------------- \r\n \r\n";
      //$file = file_put_contents('log.txt', $txt, FILE_APPEND | LOCK_EX);
      echo"Failed to connect to MYSQL<br><br> ". mysqli_connect_error();
      exit();
    }
    echo "Successfully connected to MySQL<br><br>";
    //$txt = "" .date("y-m-d") . " " . date("h:i:sa") . " " . "Successfully Connected to MYSQL\r\n";
    //$file = file_put_contents('log.txt', $txt, FILE_APPEND | LOCK_EX);
    mysqli_select_db($db, 'user_info' );
    $salt = "dskjfoewiufds".$b;
    $s = "insert into user (name,password,email) values ('$name', SHA1('$password'),'$email')";
    //echo "The SQL statement is $s";
    ($t = mysqli_query ($db,$s)) or die(mysqli_error());
    print "Registered";
     // $txt = "" .date("y-m-d") . " " . date("h:i:sa") . " " . " $name: just registered! \r\n ------------------------------------------------------------- \r\n \r\n";
     // $file = file_put_contents('log.txt', $txt, FILE_APPEND | LOCK_EX);
    return true;
}


function test($word)
{
	echo $word;
	return true;
}

/*function friendlist($name,$email) {
	( $db = mysqli_connect ('localhost', 'root', '12345678', 'user_info' ) );
    	if (mysqli_connect_errno())
    	{
      	 echo"Failed to connect to MYSQL<br><br> ". mysqli_connect_error();
     	 exit();
    	}
   	 echo "Successfully connected to MySQL<br><br>";
    	 mysqli_select_db($db, 'user_info' );
	 $s = "insert into friendlist (name, email) values ('$name', '$email')";
	 $r = "select $name, $email FROM user";
	 ($t = mysqli_query ($db, $s, $r)) or die(mysqli_error()); 
	 $num = mysqli_num_rows($t);
    	 if ($num == 0){
      	   return false;
    	   }else
           {
           print "<br>Authorized";
           return true;
    	   }
}*/

function location($zipcode,$radius){
$cSession = curl_init(); 
//step2

$zipcode = $_POST['zipcode'];
$radius = $_POST['radius'];

$date = date("Y-m-d");

$url = "http://data.tmsapi.com/v1.1/movies/showings?startDate=".$date."&zip=".$zipcode."&radius=".$radius."&units=mi&api_key=umfufqjtkt5f87335hs8a45j";

$url = str_replace(" ", '%20', $url);
curl_setopt($cSession,CURLOPT_URL, $url);
curl_setopt($cSession,CURLOPT_RETURNTRANSFER,true);
curl_setopt($cSession,CURLOPT_HEADER, false); 
curl_setopt($cSession,CURLOPT_FAILONERROR, true); 
 
//step3
$result=curl_exec($cSession);
//step4
curl_close($cSession);
}
		


function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      return auth($request['name'],$request['password']);
    case "validate_session":
	    return doValidate($request['sessionId']);
    case "register":
	    return register($request['name'],$request['password'],$request['email']);
    case "Test":
	    return test($request['message']);
    case "location":
	    return search($request['zipcode']);

  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
exit();
?>

