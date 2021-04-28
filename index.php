<?php
session_start();
include "simple_html_dom.php";

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $seasonsName = [];

    $places = [];

    $team = mb_strtolower(trim($_POST['team']));

    $html = file_get_html('https://terrikon.com/football/italy/championship/');

    $allNews = $html->find("div.news");

    if ($allNews) {

        foreach ($allNews as $news) {

            if ($news->find('a.all', 0)->plaintext === "Все Сезоны") {

                $allSeasons = file_get_html('https://terrikon.com' . $news->find('a.all', 0)->href);

                $table = $allSeasons->find('div.tab');

                $seasons = $table[0]->find('dd');

                if ($seasons) {

                    foreach ($seasons as $season) {

                        $seasonsName[] = $season->find('a', 0)->plaintext;

                        $seasonUrl = file_get_html('https://terrikon.com' . $season->find('a', 0)->href);

                        $table = $seasonUrl->find('table.big');

                        $rows = $table[0]->find('tr');

                        if ($rows) {

                            foreach ($rows as $row) {

                                if (mb_strtolower(trim($row->find('a', 0)->plaintext)) == $team) {

                                    $places[] = preg_replace('/\.+/', '', $row->find('td')[0]->plaintext);
                                    break;
                                }

                            }
                        } else {
                            throw new Exception('Отсутствуют распределение мест занятых командами');
                        }
                    }

                } else {
                    throw new Exception('Отсутствуют сезоны');
                }
            }
        }
    } else {
        throw new Exception("Отсутствуют новости");
    }

    if($seasonsName && $places){

        $res = '<div>';
        $res .= '</h1>' . 'Результаты команды: ' . $team . '</h1>';

        foreach ($seasonsName as $key => $seasonName){

            $res .= '<p style="font-size: 15px">'. 'В ' . $seasonName .' команда заняла '. $places[$key]. ' место.' .'</p>' . " ";

        }

        $res .= '</div>';
        
        $_SESSION['res'] = $res;
    }else{
        $_SESSION['res'] =  'Данные об этой команде не известны';
    }
    header('Location: /index.php');
    exit;
}

?>

<form action="index.php" method="post">
    <label for="code">Название команды: </label><br>
    <input type="text" name="team"></br>
    <button type="submit" style="padding: 10px; margin-top: 10px">Выполнить</button>
</form>

<?php
if(isset($_SESSION['res'])) echo $_SESSION['res'];

unset($_SESSION['res']);
?>




