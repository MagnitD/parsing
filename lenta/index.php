<?php

error_reporting(1);
ini_set('display_errors', 1);
setlocale(LC_ALL, 'ru_RU');
date_default_timezone_set('Europe/Moscow');
header('Content-type: text/html; charset=utf-8');

require '../classes/classes.php';
require '../classes/simple_html_dom.php';

$filename = __DIR__ . '/file.txt';
$array = file($filename, FILE_IGNORE_NEW_LINES);

$fp = fopen('fsin_1.csv', 'w');
$names = ['URL', 'TITLE', 'PRICE', 'PRICE_SALE', 'DESCRIPTION', 'PROPERTIES', 'PICTURE', 'IMAGES', 'WEIGHT', 'ACTIVITY', 'CATEGORY', 'SUBCATEGORY'];
fputcsv($fp, $names, ";");
$yy = 1;
foreach ($array as $link) {
    $page = str_get_html(curlGetPage($link));
    $title = norm($page->find('h1', 0)->plaintext);
    $price = price($page->find('meta[itemprop="price"]', 0)->content);
    $description = norm($page->find('div[itemprop="description"]', 0)->plaintext);
    $charm = [];
    $special = [];
    $images = [];
    foreach ($page->find('div[class=sku-card-tab-params__item]') as $properties) {
        $prop = norm($properties->find('dt', 0)->plaintext);
        $value = norm(!is_null($properties->find('dd', 0)) ? $properties->find('dd', 0)->plaintext : $properties->find('a', 0)->plaintext);
        $charm [$prop] = $value;
        $special [] = $prop . ':' . $value;
    }
    foreach ($page->find('div[class=sku-images-slider__carousel-item square]') as $image) {
        $images [] = $image->{'data-img-url'};
    }
    $picture = !is_null($images[0]) ? $images[0] : $page->find('div[class=sku-images-slider__image-block square__inner] img', 0)->src;
    unset($images[0]);

    $activity = norm($page->find('div[class=stock stock--none sku-store-container__stock]', 0)->plaintext);
    $activity = empty($activity) ? 'Y' : 'N';
    $weight = trim(str_replace(',', '', (strrchr($title, ','))));
    $item = [
        'url' => $link,
        'title' => $title,
        'price' => $price,
        'price_sale' => $price,
        'description' => $description,
        'properties' => implode('|', $special),
        'picture' => $picture,
        'images' => implode('|', $images),
        'weight' => $weight,
        'activity' => $activity,
        'category' => '',
        'subcategory' => '',
    ];

    echo "$yy :: " . trim($item['category']) . ": " . $item['title'] . PHP_EOL;

    fputcsv($fp, $item, ";");
    $yy++;

//    sleep(rand(13, 21));
}

fclose($fp);
