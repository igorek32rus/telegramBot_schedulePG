<?php

	function turnOffTomorrow($chat_id) {		// Функция отключения уведомлений расписания на завтра

		mb_internal_encoding('UTF-8');
		mb_http_output('UTF-8');
		
		// To DB
		try {
			include("settings.php");

			$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);
				
			$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
			$stm = $dbh->prepare($sql);
			$stm->execute($values);

			$sql = "UPDATE notify SET notify_tomorrow=0 WHERE user_id=".$chat_id;
			$stm = $dbh->prepare($sql);
			$stm->execute($values);
					
			$dbh = null;
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
		/********************************************/

		return "Оповещения о расписании на следующий день выключено. Чтобы включить: /st_on";
	}

	function turnOnTomorrow($chat_id) {		// Функция включения уведомлений расписания на завтра

		mb_internal_encoding('UTF-8');
		mb_http_output('UTF-8');
		
		// To DB
		try {
			include("settings.php");
			$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);
					
			$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
			$stm = $dbh->prepare($sql);
			$stm->execute($values);

			$sql = "UPDATE notify SET notify_tomorrow=1 WHERE user_id=".$chat_id;
			$stm = $dbh->prepare($sql);
			$stm->execute($values);
				
			$dbh = null;
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
		/********************************************/

		return "Оповещения о расписании на следующий день включено. Чтобы выключить: /st_off";
	}

	function turnOffToday($chat_id) {		// Функция отключения уведомлений расписания за час до пар

		mb_internal_encoding('UTF-8');
		mb_http_output('UTF-8');
		
		// To DB
		try {
			include("settings.php");
			$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);
					
			$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
			$stm = $dbh->prepare($sql);
			$stm->execute($values);
			
			$sql = "UPDATE notify SET notify_today=0 WHERE user_id=".$chat_id;
			$stm = $dbh->prepare($sql);
			$stm->execute($values);
					
			$dbh = null;
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
		/********************************************/

		return "Оповещения о расписании за час до начала пар выключено. Чтобы включить: /sd_on";
	}

	function turnOnToday($chat_id) {		// Функция включения уведомлений расписания за час до пар

		mb_internal_encoding('UTF-8');
		mb_http_output('UTF-8');
		
		// To DB
		try {
			include("settings.php");
			$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);
					
			$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
			$stm = $dbh->prepare($sql);
			$stm->execute($values);
			
			$sql = "UPDATE notify SET notify_today=1 WHERE user_id=".$chat_id;
			$stm = $dbh->prepare($sql);
			$stm->execute($values);
				
			$dbh = null;
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
		/********************************************/

		return "Оповещения о расписании за час до начала пар включено. Чтобы выключить: /sd_off";
	}

	function turnOffUpdates($chat_id) {		// Функция отключения уведомлений об обновлениях в расписании

		mb_internal_encoding('UTF-8');
		mb_http_output('UTF-8');
		
		// To DB
		try {
			include("settings.php");
			$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);
				
			$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
			$stm = $dbh->prepare($sql);
			$stm->execute($values);
			
			$sql = "UPDATE notify SET notify_updates=0 WHERE user_id=".$chat_id;
			$stm = $dbh->prepare($sql);
			$stm->execute($values);
					
			$dbh = null;
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
		/********************************************/

		return "Оповещения об изменениях в расписании выключено. Чтобы включить: /su_on";
	}

	function turnOnUpdates($chat_id) {		// Функция включения уведомлений об обновлениях в расписании

		mb_internal_encoding('UTF-8');
		mb_http_output('UTF-8');
		
		// To DB
		try {
			include("settings.php");
			$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);
				
			$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
			$stm = $dbh->prepare($sql);
			$stm->execute($values);
			
			$sql = "UPDATE notify SET notify_updates=1 WHERE user_id=".$chat_id;
			$stm = $dbh->prepare($sql);
			$stm->execute($values);
					
			$dbh = null;
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
		/********************************************/

		return "Оповещения об изменениях в расписании включено. Чтобы выключить: /su_off";
	}
	
?>