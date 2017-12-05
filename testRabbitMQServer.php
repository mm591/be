#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('functionLib.inc');

function doLogin($email,$password)
{
    $db = new FunctionLib();    

  if(!$db->connect())
    {	
	return array("returnCode" => '1', 'message'=>"Error connecting to server");
    }
  $data = $db->getUserInfo($email, $password);
  if($data)
    {	
	return (array('returnCode' => '0', 'message' => 'Server received request and processed'));
    }
  else
    {
	return array("returnCode" => '1', 'message'=>"Login unsuccessful");
    }

}

function doRegister($request)
{
    $db = new FunctionLib();
    
    if($db->registerUser($request['email'], $request['password']))
    {
	return array("returnCode" => '1', 'message'=>"Registration successful");
    }

    return array("returnCode" => '0', 'message'=>"<br>Registration unsuccessful<br>Username already exist!");
}

function logMessage($request)
{
	$logFile = fopen("log.txt", "a");
	fwrite($logFile, $request['message'] .'\n\n');
	return true;
}

function apiReq($request)
{
	$clientReq = new rabbitMQClient("apiExchange.ini","apiServer");
	$apiRequest = array();
	$apiRequest['type'] = "api";
	$apiRequest['brandName'] = $request['brandName'];
	$response = $clientReq->send_request($apiRequest);
	
	return $response;
}
function requestProcessor($request)
{
  echo "Request Received".PHP_EOL;
  var_dump($request);
  echo '\n' . 'End Message';
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['username'],$request['password']);
    case "register":
      return doRegister($request);
    case "log":
      return logMessage($request);
    case "api";
      return apiReq($request);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

$server->process_requests('requestProcessor');
exit();
?>
