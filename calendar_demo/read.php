<?php
header('Content-Type: application/json; charset=utf-8');
include('HttpStatusCode.php');

try{
		$pdo = new PDO("mysql:host=localhost;dbname=calendar_demo;port=3306;charset=utf8",'Luck','12345678');
	} catch(PDOException $e){
		echo "Database connection failed";
		exit;
	}

$sql = 'SELECT * FROM events WHERE id=:id';
$statement = $pdo->prepare($sql);
$statement->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
$statement->execute();

$event = $statement ->fetch(PDO::FETCH_ASSOC);
if ($event == false){
	new HttpStatusCode(400, 'No such an event.');
}
echo json_encode($event);