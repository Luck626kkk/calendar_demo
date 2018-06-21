<?php
	header('Content-Type: application/json; charset=utf-8');
	include('HttpStatusCode.php');
	
	try{
		$pdo = new PDO("mysql:host=localhost;dbname=calendar_demo;port=3306;charset=utf8",'Luck','12345678');
	} catch(PDOException $e){
		echo "Database connection failed";
		exit;
	}

	$sql = 'DELETE FROM events WHERE id=:id';
	$statement = $pdo->prepare($sql);
	$statement -> bindValue(':id', $_POST['id'], PDO::PARAM_INT);
	if($statement -> execute()){
		echo json_encode(['id' => $_POST['id']]);
	}else{
		new HttpStatusCode(400,'Wrong event.');
	}
