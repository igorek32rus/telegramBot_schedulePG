<?php

	function Schedule($day, &$time) {
		mb_internal_encoding('UTF-8');
		mb_http_output('UTF-8');

		include("settings.php");

		header("Content-Type: text/html; charset=utf-8");

		date_default_timezone_set('Etc/GMT-3');
		$date = time() * 1000;
		$date = $date + 86400000 - 86400000 * date("w");
		$date = ($date - (-180 * 60000)) - (3600 * date("G") + 60 * intval(date("i")) + (1 * intval(date("s")))) * 1000;

		//$date = 1545004800889;

		$content = file_get_contents("http://oreluniver.ru/schedule//{$IDgroup}///{$date}/printschedule");

		//echo $content."<br>";

		$obj = json_decode($content);

		function mySort($f1,$f2) {
			if ($f1->DayWeek < $f2->DayWeek) return -1;
			elseif ($f1->DayWeek > $f2->DayWeek) return 1;
			else {
				if ($f1->NumberLesson < $f2->NumberLesson) return -1;
				elseif ($f1->NumberLesson > $f2->NumberLesson) return 1;
				else return 0;
			}
		}

		uasort($obj,"mySort");


		/**
		* Класс для записи каждой пары в отдельный объект
		*/
		class Lesson
		{
			public $idGroup 		= 0;		// idGruop 				ID группы
			public $NumberSubGruop 	= 0;		// NumberSubGruop		Номер подгруппы
			public $TitleSubject 	= "";		// TitleSubject			Название предмета
			public $TypeLesson 		= "";		// TypeLesson			Тип пары (лек, практ, лаба, зачёт, конс, экз)
			public $NumberLesson 	= 0;		// NumberLesson			Номер пары
			public $DayWeek 		= 0;		// DayWeek 				День недели
			public $Korpus 			= 0;		// Korpus 				Номер корпуса
			public $NumberRoom 		= 0;		// NumberRoom 			Номер аудитории
			public $special 		= "";		// special 				Спец поле (хз для чего оно)
			public $groupName 		= "";		// title 				Имя группы (41-ПГ)
			public $employeeID 		= 0;		// employee_id			ID препода
			public $prepSurname 	= "";		// Family 				Фамилия препода
			public $prepName 		= "";		// Name 				Имя препода
			public $prepSecondName 	= "";		// SecondName 			Отчество препода

			function __construct($idGroup, $numSubGroup, $TitleSubject, $TypeLesson,
								$NumberLesson, $DayWeek, $Korpus, $NumberRoom,
								$special, $groupName, $employeeID, $prepSurname,
								$prepName, $prepSecondName)
			{
				$this->idGroup 			= $idGroup;
				$this->NumberSubGruop 	= $numSubGroup;
				$this->TitleSubject 	= $TitleSubject;
				$this->TypeLesson		= $TypeLesson;
				$this->NumberLesson		= $NumberLesson;
				$this->DayWeek 			= $DayWeek;
				$this->Korpus  			= $Korpus;
				$this->NumberRoom 		= $NumberRoom;
				$this->special 			= $special;
				$this->groupName 		= $groupName;
				$this->employeeID 		= $employeeID;
				$this->prepSurname 		= $prepSurname;
				$this->prepName 		= $prepName;
				$this->prepSecondName 	= $prepSecondName;
			}
		}

		$masLessons = array();

		foreach ($obj as $eventrType => $events) {
			foreach ($events as $event => $val) {
				//echo "{$event}: {$val}<br>";
				if ($event == 'idGruop') $idGruop = $val;
				if ($event == 'NumberSubGruop') $NumberSubGruop = $val;
				if ($event == 'TitleSubject') $TitleSubject = $val;
				if ($event == 'TypeLesson') $TypeLesson = $val;
				if ($event == 'NumberLesson') $NumberLesson = $val;
				if ($event == 'DayWeek') $DayWeek = $val;
				if ($event == 'Korpus') $Korpus = $val;
				if ($event == 'NumberRoom') $NumberRoom = $val;
				if ($event == 'special') $special = $val;
				if ($event == 'title') $title = $val;
				if ($event == 'employee_id') $employee_id = $val;
				if ($event == 'Family') $fam = $val;
				if ($event == 'Name') $im = $val;
				if ($event == 'SecondName') $otch = $val;
			}

			if (($day == "tomorrow") && ((date("N") == $DayWeek - 1) || ((date("N") == 7) && ($DayWeek == 1)))) {
				$masLessons[] = new Lesson($idGruop, $NumberSubGruop, $TitleSubject, $TypeLesson,
										   $NumberLesson, $DayWeek, $Korpus, $NumberRoom,
										   $special, $title, $employee_id, $fam,
										   $im, $otch);
			} else if (($day == "today") && (date("N") == $DayWeek)) {
				$masLessons[] = new Lesson($idGruop, $NumberSubGruop, $TitleSubject, $TypeLesson,
										   $NumberLesson, $DayWeek, $Korpus, $NumberRoom,
										   $special, $title, $employee_id, $fam,
										   $im, $otch);
			}
		}

		$reply = "";
		$first = false;		// Если первая запись, вывести заголовок расписания

		for ($i = 0; $i < 8; $i++) {
			$find = false;		// есть ли ещё записи на эту же пару (для 2 подгрупп)

			foreach ($masLessons as $lesson) {
				if ($lesson->NumberLesson == $i + 1) {
					if (!$first) {		// Если первой записи ещё не было, вставляем заголовок
						switch ($lesson->DayWeek) {
							case 1:
								$TextDayWeek = "Понедельник";
								break;
							case 2:
								$TextDayWeek = "Вторник";
								break;
							case 3:
								$TextDayWeek = "Среда";
								break;
							case 4:
								$TextDayWeek = "Четверг";
								break;
							case 5:
								$TextDayWeek = "Пятница";
								break;
							case 6:
								$TextDayWeek = "Суббота";
								break;
							default:
								# code...
								break;
						}

						if ($day == "tomorrow") {
							$reply .= "<strong>Расписание пар на завтра (".$TextDayWeek.")</strong>\n";
							$reply .= "<b>\xE2\x9A\xA0 Завтра к ".$lesson->NumberLesson." паре ";
						} else if ($day == "today") {
							$reply .= "<strong>Расписание пар на сегодня (".$TextDayWeek.")</strong>\n";
							$reply .= "<b>\xE2\x9A\xA0 Сегодня к ".$lesson->NumberLesson." паре ";
						}

						$reply .= "(";

						switch ($lesson->NumberLesson) {
							case 1:
								$time = "08:30";
								break;
							case 2:
								$time = "10:10";
								break;
							case 3:
								$time = "12:00";
								break;
							case 4:
								$time = "13:40";
								break;
							case 5:
								$time = "15:20";
								break;
							default:
								$time = "";
								break;
						}

						$reply .= $time.")</b>";

						if ($lesson->NumberLesson >= 3) {
							$reply .= " \xF0\x9F\x98\x8F\n";
						} else {
							$reply .= " \xF0\x9F\x98\x9E\n";
						}


						$first = true;
					}

					if (!$find) {
						$reply .= $lesson->NumberLesson.". ".$lesson->TitleSubject;

						if ($lesson->TypeLesson == "лек") {
							$reply .= " <i>(лекция)</i>\n";
						} elseif ($lesson->TypeLesson == "пр") {
							$reply .= " <i>(практика)</i>\n";
						} elseif ($lesson->TypeLesson == "лаб") {
							$reply .= " <i>(лаба)</i>\n";
						} elseif ($lesson->TypeLesson == "зачет") {
							$reply .= " <i>(зачёт)</i>\n";
						} elseif ($lesson->TypeLesson == "конс") {
							$reply .= " <i>(консультация)</i>\n";
						} elseif ($lesson->TypeLesson == "экз") {
							$reply .= " <i>(экзамен)</i>\n";
						}
					}

					if ($lesson->NumberSubGruop != 0) {
						$reply .= "---\n<pre>Подгруппа ".$lesson->NumberSubGruop."</pre>\n";
					}

					$reply .= "<pre>Аудитория: ".$lesson->NumberRoom."\n";

					if ($lesson->Korpus == 11) {
						$reply .= "Корпус: наугорка (11)\n";
					} elseif ($lesson->Korpus == 12) {
						$reply .= "Корпус: научка (12)\n";
					} elseif ($lesson->Korpus == 16) {
						$reply .= "Корпус: АСИ (16)\n";
					} else {
						$reply .= "Корпус: ".$lesson->Korpus."\n";
					}

					$reply .= "</pre>Препод: <a target=\"_blank\" href=\"http://oreluniver.ru/employee/".$lesson->employeeID."\">".$lesson->prepSurname." ".$lesson->prepName." ".$lesson->prepSecondName."</a>\n\n";

					$find = true;
				}
			}
		}

		// foreach ($masLessons as $element) {
		// 	foreach ($element as $key => $value) {
		// 		echo $key." ---> ".$value."<br>";
		// 	}
		// 	echo "<br>";
		// }


		if ($reply == "") {
			$reply = "error";
		}

		return $reply;
	}



	function NoSchedule($day) {
		mb_internal_encoding('UTF-8');
		mb_http_output('UTF-8');

		date_default_timezone_set('Etc/GMT-3');

		if ((date("N") != 7) && ($day == "today")) {

			$HistoryToday = "";

			try {
				include("settings.php");

				$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);

				$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
				$stm = $dbh->prepare($sql);
				$stm->execute($values);

				$stmt = $dbh->query("SELECT sch FROM history WHERE day='today'");
				while ($row = $stmt->fetch()) {
					$HistoryToday = $row['sch'];
				}

				$dbh = null;
			} catch (PDOException $e) {
				print "Error!: " . $e->getMessage() . "<br/>";
				die();
			}
			/********************************************/

			if ($HistoryToday != "") {
				return $HistoryToday;
			} else {
				return "history_error";
			}

		} elseif ((date("N") != 6) && ($day == "tomorrow")) {

			$HistoryTomorrow = "";

			try {
				include("settings.php");

				$dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);

				$sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
				$stm = $dbh->prepare($sql);
				$stm->execute($values);

				$stmt = $dbh->query("SELECT sch FROM history WHERE day='tomorrow'");
				while ($row = $stmt->fetch()) {
					$HistoryTomorrow = $row['sch'];
				}

				$dbh = null;
			} catch (PDOException $e) {
				print "Error!: " . $e->getMessage() . "<br/>";
				die();
			}
			/********************************************/

			if ($HistoryTomorrow != "") {
				return $HistoryTomorrow;
			} else {
				return "history_error";
			}

		} else {
			return "history_error";
		}

	}

?>
