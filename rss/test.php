<?php
require '../classes/simple_html_dom.php';
require '../classes/classes.php';

//$sth = $dbh->prepare("SELECT `posts`.`title`, `posts`.`created_at`, `posts`.`image_url`, `posts`.`image_big`, `posts`.`title_slug`, `posts`.`summary`, `posts`.`content`, `categories`.`name` FROM `posts`, `categories` WHERE `posts`.`category_id` = `categories`.`id` AND `posts`.`visibility` = '1';");
$domain = 'https://revenuetech.ru';
$url = 'https://revenuetech.ru/services/';
$page = str_get_html(curlGetPage($url));

$i = 1;
foreach ($page->find('div.item div.body-info a') as $category) {
    $item = str_get_html(curlGetPage($domain . $category->href));
    foreach ($item->find('div.item') as $service) {
        if (!empty($service->find('div[class="wrap shadow"] a.dark-color', 0))) {
            $image = $service->find('div.image div.wrap img', 0)->src;
            echo $service->find('div[class="wrap shadow"] a.dark-color', 0)->href . PHP_EOL;
            $item_html = str_get_html(curlGetPage($domain . $service->find('div.title a.dark-color', 0)->href));
            $text = $item_html->find('h2', 0)->outertext = '';
            $text = str_replace(' в Москве', '', $item_html->find('div.content', 1)->innertext);
            $post = [
                'title' => str_replace(' в Москве', '', $item_html->find('h1', 0)->plaintext),
                'title_slug' => $domain . $service->find('div.title a.dark-color', 0)->href,
                'image' => $domain . $image,
                'category' => trim($category->plaintext),
                'text' => trim($text),
                'date' => date('Y-m-d')
            ];
        }
    }
}