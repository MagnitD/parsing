<?php

require '../classes/classes.php';
require '../classes/simple_html_dom.php';

$filename = __DIR__ . '/file.txt';
$array = file($filename, FILE_IGNORE_NEW_LINES);

foreach ($array as $link) {

    $page = curlGetPage($link);
    $page = str_get_html($page);
    $price = !empty($page->find('.price-label__integer', 0)) ? $page->find('.price-label__integer', 0)->plaintext : 0;
//    $price = str_replace(',', '.', $price);
//    $price = (float)$price;
//    $price = round($price, 1, PHP_ROUND_HALF_UP);

    echo $link . " ::: " . $price . PHP_EOL;

}
