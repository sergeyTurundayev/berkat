<?php

require 'vendor/autoload.php';

use DiDom\Document;

$fp = fopen('result.php', "w");

$url = 'https://berkat.ru/';

// Создаем поток
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

// Открываем файл с помощью установленных выше HTTP-заголовков
$file = file_get_contents($url, false, $context);

$document = new Document($file, false);

$last_page = $document->find('.pagebar_pages a:last-child');

$last_page_link = $last_page[0]->getAttribute('href');

$pages = explode('=', $last_page_link)[1];

var_dump( $pages );

$pages_url = $url . "board?page=";

$pages = 1;

for( $i = 1; $i <= $pages; $i++ ) {

	$page_url = $pages_url . $i;

	echo "$page_url\n";

	$context = stream_context_create($opts);
	$file = file_get_contents($page_url, false, $context);
	$document = new Document($file, false);

	$products = $document->find('.board_list_item');


	foreach ($products as $product) {

		$tmp_arr = [];

		$product_html = $product->html();

		$product_html = new Document($product_html, false);

		$product_h3 = $product_html->find('h3');

		$tmp_arr["title"] = $product_h3[0]->text();

		$product_desc = $product_html->find('.board_list_item_text');

		$tmp_arr["description"] = $product_desc[0]->text();

		$name = $product_html->find('span:nth-child(5)');

		$tmp_arr["name"] = trim( $name[0]->text() );

		$city = $product_html->find('span:nth-child(7)');

		$tmp_arr["city"] = trim( $city[0]->text() );

		$phone = $product_html->find('.get_phone_style');

		if( $phone ){
			$tmp_arr["phone"] = $phone[0]->text();
		}else{
			$tmp_arr["phone"] = "-";
		}

		$price = $product_html->find('.board_list_footer_left b');

		$product_imgs = $product_html->find('.photos a');

		$product_imgs_arr = [];

		foreach ($product_imgs as $img) {
			$product_imgs_arr[] = $img->href;
		}

		$tmp_arr["images"] = $product_imgs_arr;

		// var_dump( $price );

		if( $price ){
			$price = $price[0]->text();
		} else {
			$price = "-";
		}

		$tmp_arr["price"] = $price;

		$tmp_json = json_encode( $tmp_arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

		fwrite($fp, $tmp_json . ",\n" );

	}
}


fclose( $fp );