<?php


error_reporting(1);
ini_set('display_errors', 1);
setlocale(LC_ALL, 'ru_RU');
date_default_timezone_set('Europe/Moscow');
header('Content-type: text/html; charset=utf-8');

require '../classes/classes.php';
require '../classes/simple_html_dom.php';

$fp = fopen('vam_svet_new_bra.csv', 'w');
$names = ['НАИМЕНОВАНИЕ', 'АРТИКУЛ', 'ИМЯ_БРЕНДА', 'СТРАНА_БРЕНДА', 'РАЗДЕЛ', 'КАТЕГОРИЯ', 'ЦЕНА', 'ИЗОБРАЖЕНИЕ', 'КОЛЛЕКЦИЯ', 'ОПИСАНИЕ', 'ХАРАКТЕРИСТИКИ',];
fputcsv($fp, $names, ";");
$url = 'https://www.vamsvet.ru';
$domain = 'https://www.vamsvet.ru/catalog/section/bra-i-podsvetki/';
$html = str_get_html(curlGetPage($domain));

$yy = 1;

for ($i = 1; $i <= 145; $i++) {
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
            'properties' => str_replace('PDF Файл:Открыть||', '', implode("||", $print)),

        ];

        echo "$yy :: " . trim($items['category']) . ": " . $items['title'] . PHP_EOL;

        fputcsv($fp, $items, ";");
        $yy++;

//        print_r($items);
//        die();
        sleep(rand(4, 12));

    }
    sleep(rand(9, 31));
}

fclose($fp);
