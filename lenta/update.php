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

$fp = fopen('update.csv', 'w');
$names = ['URL', 'PRICE', 'PRICE_SALE', 'STATUS', 'ACTIVITY', 'QUANTITY'];
fputcsv($fp, $names, ";");
$yy = 1;
foreach ($array as $link) {
    $page = curlGetPage($link);
    $page = str_get_html($page);
    $price = !empty($page->find('.price-label__integer', 0)) ? $page->find('.price-label__integer', 0)->plaintext : 0;
    $price = trim($price);
    $price = str_replace('&#160;', '', $price);
    $price = (int) $price;
    $activity = 'Y';
    $status = trim($page->find('div.stock', 0)->plaintext);
    if ($status == 'Товара много') {
        $quantity = 100;
    } elseif ($status == 'Товара достаточно') {
        $quantity = 50;
    } else {
        $quantity = 0;
        $price = 0;
    }


    $item = [
        'url' => $link,
        'price' => $price,
        'price_sale' => round($price * 1.20, -1, PHP_ROUND_HALF_UP),
        'status' => $status,
        'activity' => $activity,
        'quantity' => $quantity
    ];

    echo "$yy :: " . $item['url'] . " - " . $item['price'] . "::" . $item['price_sale'] . " -- " . $item['activity'] . " - " . $item['status'] . " - " . $item['quantity'] . PHP_EOL;

    fputcsv($fp, $item, ";");
    $yy++;

   sleep(rand(7, 17));
}

fclose($fp);
