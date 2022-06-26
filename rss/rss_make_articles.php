<?php

require '../classes/simple_html_dom.php';
require '../classes/classes.php';

//$sth = $dbh->prepare("SELECT `posts`.`title`, `posts`.`created_at`, `posts`.`image_url`, `posts`.`image_big`, `posts`.`title_slug`, `posts`.`summary`, `posts`.`content`, `categories`.`name` FROM `posts`, `categories` WHERE `posts`.`category_id` = `categories`.`id` AND `posts`.`visibility` = '1';");
$domain = 'https://revenuetech.ru';
$url = 'https://revenuetech.ru/articles/';
$page = str_get_html(curlGetPage($url));
$meta_title = $page->find('title', 0)->plaintext;
$meta_description = $page->find('meta[name=description]', 0)->content;
$feed = '<?xml version="1.0" encoding="utf-8"?>

<rss xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/" xmlns:turbo="http://turbo.yandex.ru" version="2.0">
    <channel>
		<title>' . $meta_title . '</title>
		<link>' . $url . '</link>
		<description>' . $meta_description . '</description>
		<language>ru</language>
		<pubDate>' . date(DATE_RFC822) . '</pubDate>
		<turbo:analytics type="Yandex" id="83471803"></turbo:analytics>';

$i = 1;
foreach ($page->find('div.info div.title a.dark-color') as $item) {
    $article = str_get_html(curlGetPage($domain . $item->href));
    $date = $article->find('div.row span.date', 0)->plaintext;
    $text = $article->find('div.content', 0)->innertext;
    $post = [
        'title' => $article->find('h1', 0)->plaintext,
        'title_slug' => $domain . $item->href,
        'text' => trim($text),
        'date' => $date
    ];

    $feed .= '<item turbo="true">
            <turbo:extendedHtml>true</turbo:extendedHtml>
            <link>' . $post['title_slug'] . '</link>
               

            <pubDate>' . date(DATE_RFC822, strtotime($post['date'])) . '</pubDate>
            <turbo:content>
            <![CDATA[
            <header>
            <h1>' . mb_strimwidth($post['title'], 0, 230, '...') . '</h1>
                         <menu>
                            <a href="' . $url . '">Статьи</a>
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
file_put_contents('rss_articles.xml', $feed);






