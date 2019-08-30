<?php
	// Рассылка расписания на завтра вечером

	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');

	include("includes/settings.php");
	include("includes/schedule.php");

	include('vendor/autoload.php'); //Подключаем библиотеку
	use Telegram\Bot\Api;

	$telegram = new Api($BotToken); //Устанавливаем токен, полученный у BotFather

	$time = "";
	$reply = Schedule("tomorrow", $time);

	$send = false;
	if (date("N") != 6) {
		$send = true;
	}

	if (($reply != "error") && ($send)) {
		// To DB
		try {
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


			// Пишем в лог
			date_default_timezone_set('Etc/GMT-3');
			$date_time = date("d.m.y H:i:s");
			$sql = "INSERT INTO log SET user_id='0', date_time='".$date_time."', action='Ежедневная рассылка расписания на завтра'";
			$stm = $dbh->prepare($sql);
			$stm->execute($values);


			for ($i=0; $i < count($mas_users); $i++) {		// Отправляем всем, кто разрешил отправлять
				$stmt = $dbh->query('SELECT notify_tomorrow FROM notify WHERE user_id='.$mas_users[$i]);

				while ($row = $stmt->fetch()) {
					if ($row['notify_tomorrow'] > 0) {
						$telegram->sendMessage([ 'chat_id' => $mas_users[$i], 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);
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

	if ($reply == "error") {
		// To DB
		try {
			$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);

			$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
			$stm = $dbh->prepare($sql);
			$stm->execute($values);

			// Пишем в лог
			date_default_timezone_set('Etc/GMT-3');
			$date_time = date("d.m.y H:i:s");
			$sql = "INSERT INTO log SET user_id='0', date_time='".$date_time."', action='Ежедневная рассылка расписания на завтра. Расписание отсутствует!'";
			$stm = $dbh->prepare($sql);
			$stm->execute($values);

			$dbh = null;
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
		/********************************************/
	}

?>
