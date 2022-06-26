<?php

error_reporting(1);
ini_set('display_errors', 1);
setlocale(LC_ALL, 'ru_RU');
date_default_timezone_set('Europe/Moscow');
header('Content-type: text/html; charset=utf-8');

require '../classes/simple_html_dom.php';

$filename = 'file.txt';
$array = file($filename, FILE_IGNORE_NEW_LINES);

function getPage($url)
{
    $headers = array(
        'cache-control: max-age=0',
        'upgrade-insecure-requests: 1',
        'user-agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36',
        'sec-fetch-user: ?1',
        'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
        'x-compress: null',
        'sec-fetch-site: none',
        'sec-fetch-mode: navigate',
        'accept-encoding: deflate, br',
        'accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIE, 'Store=0219;CityCookie=kazan');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $html = curl_exec($ch);
    curl_close($ch);
    return $html;
}

foreach ($array as $link) {
    $page = getPage($link);
    $page = str_get_html($page);
    echo $page . PHP_EOL;
    die();
//    $price = !empty($page->find('.price-label__integer', 0)) ? $page->find('.price-label__integer', 0)->plaintext : 0;
//    $price = trim($price);
//    $price = str_replace('&#160;', '', $price);
//    $price = (int)$price;
//    $status = $status = trim($page->find('div.stock', 0)->plaintext);
//
//    echo $link . ': ' . $status;
}