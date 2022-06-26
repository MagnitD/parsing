<?php

require '../classes/simple_html_dom.php';
require '../classes/classes.php';

//$sth = $dbh->prepare("SELECT `posts`.`title`, `posts`.`created_at`, `posts`.`image_url`, `posts`.`image_big`, `posts`.`title_slug`, `posts`.`summary`, `posts`.`content`, `categories`.`name` FROM `posts`, `categories` WHERE `posts`.`category_id` = `categories`.`id` AND `posts`.`visibility` = '1';");
$domain = 'https://revenuetech.ru';
$url = 'https://revenuetech.ru/services/';
$page = str_get_html(curlGetPage($url));

$feed = '<?xml version="1.0" encoding="utf-8"?>

<rss xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/" xmlns:turbo="http://turbo.yandex.ru" version="2.0">
    <channel>
		<title>Технические аудиты, обследования и экспертизы</title>
		<link>' . $domain . '</link>
		<description>Любые виды технических аудитов и обследований инженерных объектов на всех стадиях их жизни. Инженерное сопровождение и технический аутсорсинг.</description>
		<language>ru</language>
		<pubDate>' . date(DATE_RFC822) . '</pubDate>
		<turbo:analytics type="Yandex" id="83471803"></turbo:analytics>';

$i = 1;
foreach ($page->find('div.item div.body-info a') as $category) {
    $item = str_get_html(curlGetPage($domain . $category->href));
    foreach ($item->find('div.item') as $service) {
        if (!empty($service->find('div[class="wrap shadow"] a.dark-color', 0))) {
            $image = $service->find('div.image div.wrap img', 0)->src;
            $service->find('div[class="wrap shadow"] a.dark-color', 0)->href;
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

            $feed .= '<item turbo="true">
            <turbo:extendedHtml>true</turbo:extendedHtml>
            <link>' . $post['title_slug'] . '</link>
            <category>' . $post['category'] . '</category>       

            <pubDate>' . date(DATE_RFC822, strtotime($post['date'])) . '</pubDate>
            <turbo:content>
            <![CDATA[
            <header>
            <h1>' . mb_strimwidth($post['title'], 0, 230, '...') . '</h1>
            <figure>
                <img src="' . $post['image'] . '" />
                <figcaption>' . mb_strimwidth($post['title'], 0, 230, '...') . '</figcaption>
            </figure>
                         <menu>
                            <a href="https://revenuetech.ru/services/">Услуги</a>
                        </menu>
            </header>
           ' . $post['text'] . '
            ]]>
            </turbo:content>
            </item>';

            echo $i . ': ' . $post['title'] . PHP_EOL;
            $i++;
        }
    }
}
$feed .= '</channel>
</rss>';
file_put_contents('rss.xml', $feed);






