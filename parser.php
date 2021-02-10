<?php

ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);

require 'vendor/autoload.php';

use DiDom\Document;

$url = 'https://berkat.ru/';

$opts = array(
  'http'=>array(
    'method'=>"POST",
    'header'=>"Accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7\r\n" .
    "referer: https://www.work.ua/jobs/3058159/\r\n" .
    "content-type: application/json\r\n" .
    "user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36\r\n" .
    "x-requested-with: XMLHttpRequest",
    'content-length' => 0
)
);

$context = stream_context_create($opts);

$file = file_get_contents($url, false, $context);

$document = new Document($file, false);

$last_page = $document->find('.pagebar_pages a:last-child');

$last_page_link = $last_page[0]->getAttribute('href');

$pages = explode('=', $last_page_link)[1];

echo "pages: $pages\n";

$pages_url = $url . "board?page=";

$pages = 1;

$result = [];

for ($i = 1; $i <= $pages; $i++) {

	echo "page: $i\n";

	$page_url = $pages_url . $i;

	echo "$page_url\n";

	$context = stream_context_create($opts);
	$file = file_get_contents($page_url, false, $context);
	$document = new Document($file, false);

	$products = $document->find('.board_list_item');

	foreach ($products as $index => $product) {
		$result[] = [
            'title' => $product->first('h3')->text(),
            'description' => $product->first('.board_list_item_text')->text(),
            'name' => trim($product->first('span:nth-child(5)')->text()),
            'city' => trim($product->first('span:nth-child(7)')->text()),
            'phone' => $product->has('.get_phone_style') ? trim($product->first('.get_phone_style')->text()) : '-',
            'price' => $product->has('.board_list_footer_left b') ? $product->first('.board_list_footer_left b')->text() : '-',
            'images' => array_map(function ($img) {
                return $img->href;
            }, $product->find('.photos a')),
        ];

        echo $foo;
	}
}

$resultEncoded = json_encode( $result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

file_put_contents('result.json', $resultEncoded);
