<?php

require '../classes/simple_html_dom.php';
$filename = __DIR__ . '/file.txt';
$array = file($filename, FILE_IGNORE_NEW_LINES);

$headers = array(
    'cache-control: no-store, no-cache, must-revalidate',
    'upgrade-insecure-requests: 1',
    'user-agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36',
    'sec-fetch-user: ?1',
    'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
    'pragma: no-cache',
    'sec-fetch-site: none',
    'sec-fetch-mode: navigate',
    'Content-Type: text/html; charset=utf-8',
    'accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7,sr;q=0.6',
);

foreach ($array as $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'] . '/cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'] . '/cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIE, "Store=0219;CityCookie=kazan");
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (HTML, like Gecko) Chrome/91.0.4472.164 YaBrowser/21.6.4.787 Yowser/2.5 Safari/537.36');
    curl_setopt($ch, CURLOPT_REFERER, 'https://lenta.com'); // Содержимое заголовка Referer:, который будет использован в HTTP-запросе.
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $html = curl_exec($ch);
    curl_close($ch);

    $page = str_get_html($html);
    $title = trim($page->find('h1', 0)->plaintext);

    echo $title . PHP_EOL;
    sleep(rand(21, 34));
}