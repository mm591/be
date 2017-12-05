<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


class FunctionLib
{
	private $conn;

	public function connect()
	{
		try
		{
			$this->conn = new PDO('mysql:host=localhost;dbname=users', 'root', 'password');
			return true;
		}
		catch(PDOException $e)
		{
			$message =  'Error: ' . $e->getMessage();
			$client = new rabbitMQClient("testRabbitMQ.ini","testServer");      
			$request = array();
			$request['type'] = "log";
			$request['message'] = $message;
			$response = $client->publish($request);
		}
		
		return false;
	}
	public function getUserInfo($email, $password)
	{
		$this->conn = new PDO('mysql:host=localhost;dbname=users', 'root', 'password');
		$stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :em");
		$stmt->bindParam(':em', $email);
		return $stmt->execute();
		return true;
	}
	public function registerUser($email, $password)
	{

		$database = mysql_connect ('localhost', 'root', 'password');
		mysql_select_db('users');
		$query = ("SELECT * FROM users where email = '$email'");
		$t = mysql_query($query);
		if(mysql_num_rows($t) > 0)
		{
			echo "Email already in use!";
			return false;
		}
 		else
		{
			$this->conn = new PDO('mysql:host=localhost;dbname=users', 'root', 'password');
			$stmt = $this->conn->prepare("INSERT INTO users (email, password, id) VALUES (:em, :pw, NULL)");
			$stmt->bindParam(':em', $email);
			$stmt->bindParam(':pw', $password);

			return $stmt->execute();
			return true;
		}
	}
	
	public function __destruct()
	{
		$this->conn = null;
	}
}

?>
