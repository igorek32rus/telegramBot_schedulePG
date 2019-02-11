<?php

	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');

	include("includes/settings.php");			// Подключаем настройки
	include("includes/schedule.php");			// Подключаем расписание
	include("includes/notifyController.php");	// Подключаем функции по управлению уведомлениями


	include('vendor/autoload.php');				// Подключаем библиотеку
	use Telegram\Bot\Api;

	$telegram = new Api($BotToken);		// Устанавливаем токен, полученный у BotFather
	$result = $telegram -> getWebhookUpdates();		// Передаем в переменную $result полную информацию о сообщении пользователя

	$text = $result["message"]["text"];						// Текст сообщения
	$chat_id = $result["message"]["chat"]["id"];			// Уникальный идентификатор пользователя
	$message_id = $result["message"]["message_id"];			// ID сообщения (возможно для пересылки)
	$name = $result["message"]["from"]["username"];			// Юзернейм пользователя
	$first_name = $result["message"]["from"]["first_name"];	// Имя пользователя
	$last_name = $result["message"]["from"]["last_name"];	// Фамилия пользователя

	/*
	$keyboard = [["Расписание на сегодня"],
				["Расписание на завтра"],
				["Помощь", "Котейка"]]; 		//Клавиатура
	*/

	$keyboard = [["Расписание на сегодня"],
				["Расписание на завтра"],
				["Помощь"]]; 					//Клавиатура



	/******* Работаем с БД *********/
	// To DB
	try {
		$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname, $DBuser, $DBpass);

		$stmt = $dbh->query("SELECT id FROM users WHERE chat_id=".$chat_id);        // Проверяем, есть ли id этого userа

		$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
		$stm = $dbh->prepare($sql);
		$stm->execute($values);

		$create = true;

		while ($row = $stmt->fetch()) {
			if ($row['id'] > 0) {
				$create = false;	// Если такой пользователь есть в БД, не создаём новую запись
			}
		}

		$stmt->closeCursor();

		if ($create) {		// Создаём запись в БД о user'e. По сути перед первым входом в бота
			$sql = "INSERT INTO users SET chat_id='".$chat_id."', name='".$name."', first_name='".$first_name."', last_name='".$last_name."'";
			$stm = $dbh->prepare($sql);
			$stm->execute($values);
		} else {			// Если уже есть такой, просто обновляем инфу о нём
			$sql = "UPDATE users SET name='".$name."', first_name='".$first_name."', last_name='".$last_name."' WHERE chat_id='".$chat_id."'";
			$stm = $dbh->prepare($sql);
			$stm->execute($values);
		}

		$stmt->closeCursor();

		$stmt = $dbh->query("SELECT user_id FROM notify WHERE user_id=".$chat_id);        // Проверяем, есть ли id этого userа

		$create = true;

		while ($row = $stmt->fetch()) {
			if ($row['user_id'] > 0) {
				$create = false;	// Если такой пользователь есть в БД, не создаём новую запись
			}
		}

		$stmt->closeCursor();

		if ($create) {		// Создаём запись в БД о user'e. По сути перед первым входом в бота
			$sql = "INSERT INTO notify SET user_id='".$chat_id."', notify_tomorrow=1, notify_today=1, notify_updates=1";
			$stm = $dbh->prepare($sql);
			$stm->execute($values);
		}

		// Пишем в лог
		date_default_timezone_set('Etc/GMT-3');
		$date_time = date("d.m.y H:i:s");
		$sql = "INSERT INTO log SET user_id='".$chat_id."', date_time='".$date_time."', action='".$text."'";
		$stm = $dbh->prepare($sql);
		$stm->execute($values);

		$dbh = null;
	} catch (PDOException $e) {
		print "Error!: " . $e->getMessage() . "<br/>";
		die();
	}
	/********************************************/



	if ($text) {		// Проверяем что пришло
		if ($text == "/start") {
			$reply = $first_name.", добро пожаловать в бота!\nТут можно узнать расписание пар на ближайшее время. Жми на кнопки и всё увидишь. А ещё он будет присылать вам уведомления каждый день с расписанием на следующий день.";
			$reply_markup = $telegram->replyKeyboardMarkup([ 'keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => false ]);
			$telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $reply, 'reply_markup' => $reply_markup ]);
		} elseif ($text == "/help") {
			$reply = "Информация с помощью... Должна быть... но её нет. Сорянчик ;)";
			$telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $reply ]);
		} elseif ($text == "/st_off") {

			$reply = turnOffTomorrow($chat_id);

			$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);
		} elseif ($text == "/st_on") {

			$reply = turnOnTomorrow($chat_id);

			$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);
		} elseif ($text == "/sd_off") {

			$reply = turnOffToday($chat_id);

			$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);
		} elseif ($text == "/sd_on") {

			$reply = turnOnToday($chat_id);

			$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);
		} elseif ($text == "/su_off") {

			$reply = turnOffUpdates($chat_id);

			$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);
		} elseif ($text == "/su_on") {

			$reply = turnOnUpdates($chat_id);

			$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);
		} elseif (($text == "Котейка") || ($text == "/cat")) {
			//$url = "https://fanzon-portal.ru/upload/blog/avatar/630/starcraft_cat.jpg";
			$url = "https://loremflickr.com/500/500/cat,kitten";
			$telegram->sendPhoto([ 'chat_id' => $chat_id, 'photo' => $url, 'caption' => "Просто котейка :) (/cat)" ]);

		} elseif ($text == "Расписание на завтра") {

			$time = "";
			$reply = Schedule("tomorrow", $time);

			if ($reply == "error") {
				$reply = "К сожалению расписание на завтра отсутствует...";

				if (date("N") != 6) {
					$reply .= " Либо сайт с расписанием прилёг.";

					$history = NoSchedule("tomorrow");
					if ($history != "history_error") {
						$reply .= "\nВот сохранённое расписание, пока сайт лежит...\n\n";
						$reply .= $history;
					} else {
						$reply = "Ошибка!";
					}
				}
			}

			$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);
		} elseif ($text == "Расписание на сегодня") {

			$time = "";
			$reply = Schedule("today", $time);

			if ($reply == "error") {
				$reply = "К сожалению расписание на сегодня отсутствует...";

				if (date("N") != 7) {
					$reply .= " Либо сайт с расписанием прилёг.";

					$history = NoSchedule("today");
					if ($history != "history_error") {
						$reply .= "\nВот сохранённое расписание, пока сайт лежит...\n\n";
						$reply .= $history;
					} else {
						$reply = "Ошибка!";
					}
				}

			}

			$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);

		} elseif ($text == "Помощь") {

			$reply = "Это публичная β, так что не судите строго. Есть предложения по улучшению или нашли баг? Пишите <a href='https://t.me/igorek32rus'>сюда</a>.\n\n";
			$reply .= "<b>Команды бота:</b>\n";
			$reply .= "\xE2\x96\xB6 /s Номер_дня_недели - Расписание пар на указанный день недели;\n";
			$reply .= "\xE2\x96\xB6 /prep Имя_препода - Поиск преподавателя \"Имя_препода\";\n";
			$reply .= "\xE2\x96\xB6 [/sd_on][/sd_off] - Включить/Отключить оповещения с расписанием за час до начала пар;\n";
			$reply .= "\xE2\x96\xB6 [/st_on][/st_off] - Включить/Отключить оповещения с расписанием на следующий день;\n";
			$reply .= "\xE2\x96\xB6 [/su_on][/su_off] - Включить/Отключить оповещения об изменениях в расписании;\n";

			if ($chat_id == $IDadmin) {
				$reply .= "\n\n<b>КОМАНДЫ АДМИНА:</b>\n\xE2\x9A\xA1 /admin [сообщение] - сообщение от админа в чат;\n\xE2\x9A\xA1 /users - список пользователей бота;\n\xE2\x9A\xA1 /whois [chat_id] - инфа о пользователе с chat_id;\n\xE2\x9A\xA1 /log - вывод последних 10 действий с ботом.";
			}

			$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);

		} elseif ($text == "/users") {

			include("includes/adminFunctions.php");
			$reply = listUsers($chat_id, $text);

			if ($reply == "error1") {
				$reply = "А ты админ? Чёт не похож :)";
			}

			$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);

		} elseif ($text == "/log") {

			include("includes/adminFunctions.php");
			$reply = showLog($chat_id, $text);

			if ($reply == "error1") {
				$reply = "А ты админ? Чёт не похож :)";
			}

			$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);

		} elseif ((stripos($text, "/whois") !== false) && (stripos($text, "/whois") == 0)) {

			include("includes/adminFunctions.php");
			$reply = whoIs($chat_id, $text);

			if ($reply == "error1") {
				$reply = "После /whois должен идти Chat ID...";
			} elseif ($reply == "error2") {
				$reply = "А ты админ? Чёт не похож :)";
			}

			$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);

		} elseif ((stripos($text, "/prep") !== false) && (stripos($text, "/prep") == 0)) {
			$textMessage = $text;
			$textMessage = substr($textMessage, 6);		// Убираем из текста "/prep "

			if (mb_strlen($textMessage) >= 3) {
				// To DB
				try {
					include("includes/settings.php");			// Подключаем настройки
					$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname, $DBuser, $DBpass);

					$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
					$stm = $dbh->prepare($sql);
					$stm->execute($values);

					$stmt = $dbh->query("SELECT * FROM employee WHERE fio LIKE '%$textMessage%'");

					$search = $textMessage;
					$textMessage = "<b>Результаты поиска по *".$textMessage."*:</b>\n";

					$count = 0;
					while ($row = $stmt->fetch())	// Рассылаем всем сообщение от админа
					{
						$textMessage .= "\xE2\x96\xAA <a href=\"http://oreluniver.ru/employee/".$row['db_id']."\">".$row['fio']."</a>\n";
						$count++;
					}

					if ($count == 0) {
						$textMessage = "Поиск по *".$search."* не дал результатов...";
					}

					$dbh = null;
				} catch (PDOException $e) {
					print "Error!: " . $e->getMessage() . "<br/>";
					die();
				}
				/********************************************/
			} else {
				$textMessage = "Ошибка! Минимальное количество символов для поиска - 3.";
			}

			$reply = $textMessage;

			$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);

		} elseif ((stripos($text, "/admin") !== false) && (stripos($text, "/admin") == 0)) {

			include("includes/adminFunctions.php");
			$reply = adminMessage($chat_id, $text);

			if ($reply == "error1") {
				$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => "После /admin должен идти текст..." ]);
			} elseif ($reply == "error2") {
				$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => "А ты админ? Чёт не похож :)" ]);
			} else {
				// To DB
				try {
					$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname, $DBuser, $DBpass);

					$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
					$stm = $dbh->prepare($sql);
					$stm->execute($values);

					$stmt = $dbh->query('SELECT * FROM users');
					while ($row = $stmt->fetch())	// Рассылаем всем сообщение от админа
					{
						$telegram->sendMessage([ 'chat_id' => $row['chat_id'], 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);
						usleep(333333);     // ждём 1/3 секунды и отправляем следующему
					}

					$dbh = null;
				} catch (PDOException $e) {
					print "Error!: " . $e->getMessage() . "<br/>";
					die();
				}
				/********************************************/
			}

		} elseif ((stripos($text, "/s ") !== false) && (stripos($text, "/s ") == 0)) {

			$reply = substr($text, 3);		// Убираем из текста "/s "

			$time = "";
			$reply = Schedule($reply, $time);

			if ($reply == "error") {
				$reply = "Ошибка!";
			}

			$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => $reply ]);

		} else {
			$reply = "По запросу \"<b>".$text."</b>\" ничего не найдено.";
			$reply_markup = $telegram->replyKeyboardMarkup([ 'keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => false ]);
			$telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $reply, 'parse_mode' => 'HTML', 'reply_markup' => $reply_markup ]);
		}

	} else {
		$reply = "Отправьте текстовое сообщение.";
		$reply_markup = $telegram->replyKeyboardMarkup([ 'keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => false ]);
		$telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $reply, 'parse_mode' => 'HTML', 'reply_markup' => $reply_markup ]);
		//$telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => "Отправьте текстовое сообщение." ]);
	}

?>
