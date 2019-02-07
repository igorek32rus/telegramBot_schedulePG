<?php
    // Обновление преподов
    header('Content-Type: text/html; charset=utf-8');

    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');
    date_default_timezone_set('Etc/GMT-3');

    include("includes/settings.php");
    include('includes/simple_html_dom.php');

    try {
        $dbh = new PDO("mysql:host=".$DBhost.";dbname=".$DBname.";charset=utf8mb4", $DBuser, $DBpass);

        $sql = "TRUNCATE TABLE employee";
        $stm = $dbh->prepare($sql);
        $stm->execute($values);

        $sql = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'";
        $stm = $dbh->prepare($sql);
        $stm->execute($values);

        $html = new simple_html_dom();
        $html->load_file('http://oreluniver.ru/employee');
        $fulltext = iconv("Windows-1251", "UTF-8", $html->innertext);
        $html->load($fulltext);

        $element = $html->find("div a[itemprop=fio]");

        $sql = "INSERT INTO employee (db_id, fio) VALUES ";

        foreach ($element as $temp) {
            $prep_id = substr($temp->href, 10);     // убираем /employee/ из адреса, оставляя только ID
            $fio = substr($temp->innertext, 1);     // в оригинале вначале пробел
            $fio = str_replace('&nbsp;', ' ', $fio);
            $sql .= "('".$prep_id."', '".$fio."'), ";
        }

        $sql = substr($sql, 0, -2);
        $sql .= ";";

        $stm = $dbh->prepare($sql);
        $stm->execute($values);

        $dbh = null;
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        die();
    }
    /********************************************/

?>
