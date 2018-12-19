#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


function auth($n, $p) {
    ( $db = mysqli_connect ( 'localhost', 'root', '12345678', 'user_info' ) );
    if (mysqli_connect_errno())
    {
      echo"Failed to connect to MYSQL<br><br> ". mysqli_connect_error();
      exit();
    }
    echo "Successfully connected to MySQL<br><br>";
    mysqli_select_db($db, 'user_info' );
    $s = "select * from user where name = '$n' and password = SHA1('$p')";
    //echo "The SQL statement is $s";
    ($t = mysqli_query ($db,$s)) or die(mysqli_error());
    $num = mysqli_num_rows($t);
    if ($num == 0){
      return false;
    }else
    {
      print "<br>Authorized";
      return true;
    }
}

function register($a,$b,$c) {
    ( $db = mysqli_connect ( 'localhost', 'root', '12345678', 'user_info' ) );
    if (mysqli_connect_errno())
    {
      echo"Failed to connect to MYSQL<br><br> ". mysqli_connect_error();
      exit();
    }
    echo "Successfully connected to MySQL<br><br>";
    mysqli_select_db($db, 'user_info' );
    $salt = "dskjfoewiufds".$b;
    $s = "insert into user (name,password,email) values ('$a', SHA1('$b'),'$c')";
    //echo "The SQL statement is $s";
    ($t = mysqli_query ($db,$s)) or die(mysqli_error());
    print "Registered";
    return true;
}


function test($word)
{
	echo $word;
	return true;
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
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
exit();
?>

