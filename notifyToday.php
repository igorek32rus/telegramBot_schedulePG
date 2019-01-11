<?php
	// Оповещение всех за час до пар

	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');
	
	include("includes/settings.php");	// Подключаем настроки
	include("includes/schedule.php");	// Подключаем расписание

	include('vendor/autoload.php');		//Подключаем библиотеку
	use Telegram\Bot\Api; 

	$telegram = new Api($BotToken);		//Устанавливаем токен, полученный у BotFather

	$send = true;	// Будем ли отправлять всем расписание за час 

	$time = "";		// Получаем время начала первой пары
	$reply = Schedule("today", $time);		// Получаем расписание

	date_default_timezone_set('Etc/GMT-3');
	if ((date("H:i") == date("H:i", strtotime($time."+04:00"))) && (date("N") != 7)) {	// Если текущее время = time на сегодня в расписании -1:00 И не воскресенье
		if (($send) && ($reply != "error")) {	// Если нужно отпралять и расписание получено
			// To DB
			try {
				$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);

				$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
				$stm = $dbh->prepare($sql);
				$stm->execute($values);

				$stmt = $dbh->query('SELECT * FROM users');

				$mas_users = array();
				$i = 0;

				while ($row = $stmt->fetch()) {		// Получаем список всех chat_id
					$mas_users[$i] = $row['chat_id'];
					$i++;
				}

				$stmt->closeCursor();


				// Пишем в лог
				date_default_timezone_set('Etc/GMT-3');
				$date_time = date("d.m.y H:i:s");
				$sql = "INSERT INTO log SET user_id='0', date_time='".$date_time."', action='Рассылка расписания за час до начала пар'";
				$stm = $dbh->prepare($sql);
				$stm->execute($values);


				$new_reply = "<b>До начала пар остался ровно час!</b>\n";
				$new_reply .= $reply;

				for ($i=0; $i < count($mas_users); $i++) {		// Отправляем всем, кто разрешил отправлять
					$stmt = $dbh->query('SELECT notify_today FROM notify WHERE user_id='.$mas_users[$i]);

					while ($row = $stmt->fetch()) {
						if ($row['notify_today'] > 0) {
							$telegram->sendMessage([ 'chat_id' => $mas_users[$i], 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $new_reply ]);
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
	
?>