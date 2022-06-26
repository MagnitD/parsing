<?php

error_reporting(1);
ini_set('display_errors', 1);
setlocale(LC_ALL, 'ru_RU');
date_default_timezone_set('Europe/Moscow');
header('Content-type: text/html; charset=utf-8');

require '../classes/simple_html_dom.php';

function curlGetPage($url, $cookie)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.164 YaBrowser/21.6.4.787 Yowser/2.5 Safari/537.36');
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, 'https://lenta.com');
    curl_setopt($ch, CURLOPT_COOKIE, "CustomerId=7050ec4acf7544f1ad6e33a30d60b7fc;Store=0219;CityCookie=kazan;cookiesession1=678B286AXZABCEFGHIJKLMNOPQRSB3AE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;

}

$site = 'fsin-alga.ru';

if ($site == 'fsin-alga.ru') {
    $store = '0219';
    $city = 'kazan';
    $customer = '7050ec4acf7544f1ad6e33a30d60b7fc';
    $session = '678B286AXZABCEFGHIJKLMNOPQRSB3AE';
    $file = 'file.txt';
    $export = 'update_new.csv';

} elseif ($site == 'ik2-market.ru') {
    $store = '0011';
    $city = 'spb';
    $customer = '7050ec4acf7544f1ad6e33a30d60b7fc';
    $session = '678B286AXZABCEFGHIJKLMNOPQRSB3AE';
    $file = 'file_spb.txt';
    $export = 'update_new_spb.csv';
}
$mode = 'w';
$names = ['URL', 'PRICE', 'PRICE_SALE', 'STATUS', 'ACTIVITY', 'QUANTITY'];
$cookie = "CustomerId=$customer;Store=$store;CityCookie=$city;cookiesession1=$session";


$array = file($file, FILE_IGNORE_NEW_LINES);
$fp = fopen($export, $mode);
fputcsv($fp, $names, ";");
$yy = 1;
foreach ($array as $link) {
    $page = curlGetPage($link, $cookie);
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

    sleep(rand(3, 7));
}

fclose($fp);
