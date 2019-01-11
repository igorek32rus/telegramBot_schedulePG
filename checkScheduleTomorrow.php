<?php
	// Проверка, изменилось ли расписание и обновление базы, если день сменился

	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');

	include("includes/settings.php");
	include("includes/schedule.php");

	date_default_timezone_set('Etc/GMT-3');

	// Обновяем дату, если новая
	try {
		$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);

		$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
		$stm = $dbh->prepare($sql);
		$stm->execute($values);

			
		$stmt = $dbh->query("SELECT val FROM settings WHERE type=\"now_date\"");
		while ($row = $stmt->fetch()) {
			$text = $row['val'];

			$date_today = date("d.m.y");

			if ($date_today != $text) {
				$stmt->closeCursor();

				$sql = "UPDATE settings SET val='".$date_today."' WHERE type='now_date'";
				$stm = $dbh->prepare($sql);
				$stm->execute($values);
			}
		}
			
		$dbh = null;
	} catch (PDOException $e) {
		print "Error!: " . $e->getMessage() . "<br/>";
		die();
	}
	/********************************************/


	$time = "";
	$schedule_tomorrow = Schedule("tomorrow", $time);

	$MD5_Schedule_tomorrow = md5($schedule_tomorrow);

	$DB_key_tomorrow = "";

	try {
		$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);
			
		$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
		$stm = $dbh->prepare($sql);
		$stm->execute($values);

		$stmt = $dbh->query("SELECT text FROM history WHERE day=\"today\"");
		while ($row = $stmt->fetch()) {
			$DB_key_tomorrow = $row['text'];
		}
		
		$stmt->closeCursor();

		$dbh = null;
	} catch (PDOException $e) {
		print "Error!: " . $e->getMessage() . "<br/>";
		die();
	}
	/********************************************/
	
	if ($MD5_Schedule_tomorrow != $DB_key_tomorrow) {
		if ($schedule_tomorrow == "error") {		// Пришёл error
			if ((date("G") == 23) || (date("G") == 0)) {	// Время 23-1
				try {
					$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);

					$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
					$stm = $dbh->prepare($sql);
					$stm->execute($values);

					$sql = "UPDATE history SET text='".$MD5_Schedule_tomorrow."', sch='' WHERE day='tomorrow'";
					$stm = $dbh->prepare($sql);
					$stm->execute($values);

					$dbh = null;
				} catch (PDOException $e) {
					print "Error!: " . $e->getMessage() . "<br/>";
					die();
				}
				/********************************************/
			}
		} else {		// Пришёл не error
			try {		// Обновляем ключ и текст в БД
				$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);

				$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
				$stm = $dbh->prepare($sql);
				$stm->execute($values);

				$sql = "UPDATE history SET text='".$MD5_Schedule_tomorrow."', sch='".$schedule_tomorrow."' WHERE day='tomorrow'";
				$stm = $dbh->prepare($sql);
				$stm->execute($values);

				$dbh = null;
			} catch (PDOException $e) {
				print "Error!: " . $e->getMessage() . "<br/>";
				die();
			}
			/********************************************/

			if ((date("G") != 23) && (date("G") != 0)) {	// Время 23-1
				$replyTomorrow = "<b>!!! ВНИМАНИЕ !!!\nПроизошло обновление расписания пар на завтра!</b>\n".$schedule_tomorrow;

				try {		// Оповещаем всех, кто разрешил
					$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);

					$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
					$stm = $dbh->prepare($sql);
					$stm->execute($values);
		
					$stmt = $dbh->query('SELECT * FROM users');

					$mas_users = array();
					$i = 0;

					while ($row = $stmt->fetch()) {
						$mas_users[$i] = $row['chat_id'];
						$i++;
					}

					$stmt->closeCursor();

					for ($i=0; $i < count($mas_users); $i++) {		// Отправляем всем, кто разрешил отправлять
						$stmt = $dbh->query('SELECT notify_updates FROM notify WHERE user_id='.$mas_users[$i]);

						while ($row = $stmt->fetch()) {
							if ($row['notify_updates'] > 0) {
								$telegram->sendMessage([ 'chat_id' => $row['chat_id'], 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $replyTomorrow ]);
							}
						}

						usleep(333333);     // ждём 1/3 секунды и отправляем следующему

						$stmt->closeCursor();
					}

					$dbh = null;
				} catch (PDOException $e) {
					print "Error!: " . $e->getMessage() . "<br/>";
					die();
				}
				/********************************************/			
			}
		}
	}

	/***************** КОНЕЦ РАСПИСАНИЯ НА ЗАВТРА *****************/
	
?>