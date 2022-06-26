<?php


error_reporting(1);
ini_set('display_errors', 1);
setlocale(LC_ALL, 'ru_RU');
date_default_timezone_set('Europe/Moscow');
header('Content-type: text/html; charset=utf-8');
function curlGetPage($url, $referer = 'https://vamsvet.com')
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.164 YaBrowser/21.6.4.787 Yowser/2.5 Safari/537.36');
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    curl_setopt ($ch, CURLOPT_COOKIEFILE, "cookie.txt");
    curl_setopt ($ch, CURLOPT_COOKIEJAR, "cookie.txt");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;

}

require '../classes/simple_html_dom.php';

$fp = fopen('vam_svet_svet.csv', 'w');
$names = ['НАИМЕНОВАНИЕ', 'АРТИКУЛ', 'ИМЯ_БРЕНДА', 'СТРАНА_БРЕНДА', 'РАЗДЕЛ', 'КАТЕГОРИЯ', 'ЦЕНА', 'ИЗОБРАЖЕНИЕ', 'КОЛЛЕКЦИЯ', 'ОПИСАНИЕ', 'ХАРАКТЕРИСТИКИ',];
fputcsv($fp, $names, ";");
$url = 'https://www.vamsvet.ru';
$domain = 'https://www.vamsvet.ru/catalog/section/svetilniki/';
$html = str_get_html(curlGetPage($domain));

$yy = 1;

for ($i = 1; $i <= 588; $i++) {
    $page = file_get_contents($domain . '?catalog_ajax_call=Y&PAGEN_1=' . $i);
    $page = json_decode($page, true);
    $page = implode(' ', $page);
    $page = str_get_html($page);
//    $page = str_get_html(curlGetPage($domain . '?catalog_ajax_call=Y&PAGEN_1=' . $i . '/'));
    foreach ($page->find('a[class=prod__name js-cd-link]') as $item) {
        $html_item = str_get_html(curlGetPage($url . $item->href));
        $name = $html_item->find('h1', 0)->plaintext;
        if (!is_null($html_item->find('div[class=product-fix__p]', 0))) {
            $price = str_replace(['₽', ' '], ['', ''], trim($html_item->find('div[class=product-fix__p]', 0)->plaintext));
        } else {
            $price = 'Под заказ';
        }
        $description = $html_item->find('.pr-page__text', 0)->plaintext;
        $article = trim($html_item->find('.prod-tec__value', 0)->plaintext);
        $properties = [];
        $print = [];
        foreach ($html_item->find('.prod-tec__car') as $property) {
            $prop = trim($property->find('.prod-tec__name span', 0)->plaintext);
            $value = trim(str_replace(['Посмотреть другие товары
данной категории', 'При условии использования светодиодных ламп'], ['', ''], $property->find('.prod-tec__value', 0)->plaintext));
            $properties [$prop] = $value;
            $print [] = $prop . ':' . $value;
        }

        $images = [];
        foreach ($html_item->find('.swiper-wrapper a[data-fancybox=el_gallery]') as $image) {
            $images [] = $url . $image->href;
        }


        $items = [
            'title' => html_entity_decode($name),
            'article' => $article,
            'brand_name' => $properties['Бренд'],
            'brand_country' => $properties['Страна бренда'],
            'category' => $properties['Раздел'],
            'subcategory' => $properties['Каталог'],
            'price' => $price,
            'images' => implode("|", $images),
            'collection' => $properties['Коллекция'],
            'description' => trim($description),
            'properties' => str_replace(['PDF Файл:Открыть||', 'ДаПри'], ['', 'Да при'], implode("||", $print)),

        ];

        echo "$yy :: " . trim($items['category']) . ": " . $items['title'] . PHP_EOL;

        fputcsv($fp, $items, ";");
        $yy++;

//        print_r($items);
//        die();
//        sleep(rand(4, 12));

    }
//    sleep(rand(9, 31));
}

fclose($fp);
