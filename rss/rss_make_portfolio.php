<?php

require '../classes/simple_html_dom.php';
require '../classes/classes.php';

//$sth = $dbh->prepare("SELECT `posts`.`title`, `posts`.`created_at`, `posts`.`image_url`, `posts`.`image_big`, `posts`.`title_slug`, `posts`.`summary`, `posts`.`content`, `categories`.`name` FROM `posts`, `categories` WHERE `posts`.`category_id` = `categories`.`id` AND `posts`.`visibility` = '1';");
$domain = 'https://revenuetech.ru';
$url = 'https://revenuetech.ru/portfolio/';
$page = str_get_html(curlGetPage($url));
$meta_title = $page->find('title', 0)->plaintext;
$meta_description = $page->find('meta[name=description]', 0)->content;

$feed = '<?xml version="1.0" encoding="utf-8"?>

<rss xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/" xmlns:turbo="http://turbo.yandex.ru" version="2.0">
    <channel>
		<title>Портфолио центра технического аудита revenuetech.ru</title>
		<link>' . $url . '</link>
		<description>Выполненные работы и успешные кейсы центра технического аудита revenuetech.ru</description>
		<language>ru</language>
		<pubDate>' . date(DATE_RFC822) . '</pubDate>
		<turbo:analytics type="Yandex" id="83471803"></turbo:analytics>';

$i = 1;
foreach ($page->find('div.item-views div.wrap a') as $item) {
    $link = $domain . $item->href;
    $item = str_get_html(curlGetPage($domain . $item->href));
    $text = $item->find('h2', 0)->outertext = '';
    $image = $item->find('div[class="inner items"] a.fancybox', 0)->href;
    $text = str_replace(' в Москве', '', $item->find('div.content', 0)->innertext);
    $post = [
        'title' => str_replace(' в Москве', '', $item->find('h1', 0)->plaintext),
        'title_slug' => $link,
        'image' => $domain . $image,
        'category' => trim($item->find('div.detail div.col-md-12 div.properties div.property div.value', 0)->plaintext),
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
                            <a href="'. $url . '">Портфолио</a>
                        </menu>
            </header>
           ' . $post['text'] . '
            ]]>
            </turbo:content>
            </item>';

    echo $i . ': ' . $post['title'] . PHP_EOL;
    $i++;
}
$feed .= '</channel>
</rss>';
file_put_contents('rss_portfolio.xml', $feed);






