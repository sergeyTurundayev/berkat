<?php

ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);

require 'vendor/autoload.php';

use DiDom\Document;

$url = 'https://berkat.ru/';

$opts = array(
  'http'=>array(
    'method'=>'POST',
    'header'=>'Accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7' . "\r\n" .
    'referer: https://www.work.ua/jobs/3058159/' . "\r\n" .
    'content-type: application/json' . "\r\n" .
    'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36' . "\r\n" .
    'x-requested-with: XMLHttpRequest',
    'content-length' => 0
)
);

$context = stream_context_create($opts);

$file = file_get_contents($url, false, $context);

$document = new Document($file, false);

$lastPage = $document->find('.pagebar_pages a:last-child');

$lastPageLink = $lastPage[0]->getAttribute('href');

$pages = explode('=', $lastPageLink)[1];

echo 'pages: ' . $pages . PHP_EOL;

$pagesUrl = $url . 'board?page=';

$result = [];

for ($i = 1; $i <= $pages; $i++) {

    if($i == 2) break; // delete

    echo 'page: ' . $i . PHP_EOL;

    $pageUrl = $pagesUrl . $i;

    echo $pageUrl . PHP_EOL;

    $context = stream_context_create($opts);
    $file = file_get_contents($pageUrl, false, $context);
    $document = new Document($file, false);

    $productsEl = $document->find('.board_list_item');

    foreach ($productsEl as $index => $productEl) {
      $result[] = [
        'title' => $productEl->first('h3')->text(),
        'description' => $productEl->first('.board_list_item_text')->text(),
        'name' => trim($productEl->first('span:nth-child(5)')->text()),
        'city' => trim($productEl->first('span:nth-child(7)')->text()),
        'phone' => $productEl->has('.get_phone_style') ? trim($productEl->first('.get_phone_style')->text()) : '-',
        'price' => $productEl->has('.board_list_footer_left b') ? $productEl->first('.board_list_footer_left b')->text() : '-',
        'images' => array_map(function ($img) {
            return $img->href;
        }, $productEl->find('.photos a')),
    ];
}
}

$resultEncoded = json_encode( $result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

file_put_contents('result.json', $resultEncoded);