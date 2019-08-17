<?php
    // Обновление преподов
    header('Content-Type: text/html; charset=utf-8');

    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');
    date_default_timezone_set('Etc/GMT-3');

    include("includes/settings.php");
    include('includes/simple_html_dom.php');

    $dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);

    $sql = "TRUNCATE TABLE employee";
    $stm = $dbh->prepare($sql);
    $stm->execute($values);

    $sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
    $stm = $dbh->prepare($sql);
    $stm->execute($values);

    $sql = "INSERT INTO employee (db_id, fio) VALUES ";

    for ($i=1; $i < 32; $i++) {

        $url = 'http://oreluniver.ru/employee';
        $data = array('type' => 'updateEmployee', 'letter' => $i);

        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE) {  }

        $obj = json_decode($result);

        $result = $obj->employeeList;

        //echo $bodytag; // <body text=black>
        $result1 = "<html>".$result."</html>";

        $html = new simple_html_dom();
        $html = str_get_html($result1);

        $element = $html->find("a[target]");

        foreach ($element as $temp) {
            $prep_id = substr($temp->href, 10);     // убираем /employee/ из адреса, оставляя только ID
            $prep_id = preg_replace('/\D/', '', $prep_id);
            $fio = substr($temp->innertext, 1);     // в оригинале вначале пробел
            $fio = str_replace('&nbsp;', ' ', $fio);
            $sql .= "(".$prep_id.", \"".$fio."\"), ";
            //echo $prep_id." - <a href=\"http://oreluniver.ru/employee/".$prep_id."\">".$fio."</a><br>";
        }

        $html->clear(); // подчищаем за собой
    }

    $sql = substr($sql, 0, -2);
    $sql .= ";";

    echo $sql;

    $stm = $dbh->prepare($sql);
    $stm->execute($values);
    $dbh = null;
    unset($dbh);
    unset($html);
?>
