<?php

	function adminMessage($chat_id, $textMessage) {
		include("settings.php");

		if ($chat_id == $IDadmin) {
				
			$reply = substr($textMessage, 7);		// Убираем из текста "/admin "

			if ($reply != "0") {
				return $reply;
			} else {
				return "error1";	// После /admin ничего не идёт
			}

		} else {
			return "error2";		// Не админ
		}
	}

	function listUsers($chat_id, $textMessage) {
		mb_internal_encoding('UTF-8');
		mb_http_output('UTF-8');

		include("settings.php");

		if ($chat_id == $IDadmin) {
				
			// To DB
			try {
				$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);
						
				$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
				$stm = $dbh->prepare($sql);
				$stm->execute($values);

				$stmt = $dbh->query('SELECT * FROM users');
				$list = "<b>Список всех пользователей:</b>\n";

				while ($row = $stmt->fetch()) {		// Получаем список всех chat_id
					$list .= "\n";
					$list .= "ID: ".$row['id']."\nChat ID: ".$row['chat_id']."\nUsername: <a href='https://t.me/".$row['name']."'>".$row['name']."</a>\nИмя: ".$row['first_name']."\nФамилия: ".$row['last_name']."\n";
				}
						
				$dbh = null;
			} catch (PDOException $e) {
				print "Error!: " . $e->getMessage() . "<br/>";
				die();
			}
			/********************************************/

			return $list;

		} else {
			return "error1";		// Не админ
		}
	}


	function showLog($chat_id, $textMessage) {
		mb_internal_encoding('UTF-8');
		mb_http_output('UTF-8');

		include("settings.php");

		if ($chat_id == $IDadmin) {
				
			// To DB
			try {
				$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);
						
				$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
				$stm = $dbh->prepare($sql);
				$stm->execute($values);
					
				$stmt = $dbh->query('SELECT * FROM (SELECT * FROM log ORDER BY id DESC LIMIT 10) AS T ORDER BY id ASC');
				$log = "<b>Последние 10 действий:</b>\n";

				while ($row = $stmt->fetch()) {		// Получаем список всех chat_id
					//$log .= "\n";
					$log .= "\xF0\x9F\x95\x91 [".$row['date_time']."] ".$row['user_id'].": ".$row['action']."\n";
				}
							
				$dbh = null;
			} catch (PDOException $e) {
				print "Error!: " . $e->getMessage() . "<br/>";
				die();
			}
			/********************************************/

			return $log;
	
		} else {
			return "error1";		// Не админ
		}
	}




	function whoIs($chat_id, $textMessage) {
		mb_internal_encoding('UTF-8');
		mb_http_output('UTF-8');

		include("settings.php");

		if ($chat_id == $IDadmin) {
				
			$reply = substr($textMessage, 7);		// Убираем из текста "/whois "

			if ($reply != "0") {

				// To DB
				try {
					$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);
						
					$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
					$stm = $dbh->prepare($sql);
					$stm->execute($values);

					$stmt = $dbh->query('SELECT * FROM users WHERE chat_id='.$reply);
					$info = "<b>Инфа о ".$reply.":</b>\n";

					while ($row = $stmt->fetch()) {		// Получаем список всех chat_id
						$rep .= "\n";
						$rep .= "ID: ".$row['id']."\nChat ID: ".$row['chat_id']."\nUsername: <a href='https://t.me/".$row['name']."'>".$row['name']."</a>\nИмя: ".$row['first_name']."\nФамилия: ".$row['last_name']."\n";
					}
							
					$dbh = null;
				} catch (PDOException $e) {
					print "Error!: " . $e->getMessage() . "<br/>";
					die();
				}
				/********************************************/

				if ($rep == "") {
					$info = "Информация о ".$reply." не найдена!";
				} else {
					$info .= $rep;
				}

				return $info;

			} else {
				return "error1";	// Неверная команда

			}

		} else {
			return "error2";		// Не админ
		}
	}
?>