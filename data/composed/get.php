<?php
require __DIR__ . '/../paper_items/get.php';

$layerOrder = [9, 1, 7, 5, 4, 3, 2, 6, 8];

$dataString = pathinfo(parse_url($_SERVER['REQUEST_URI'],  PHP_URL_PATH), PATHINFO_FILENAME);

if(!preg_match('/^\d+\|\d+\|\d+\|\d+\|\d+\|\d+\|\d+\|\d+\|\d+$/', $dataString)) {
	http_response_code(400);
	echo 'Bad Request';
	die;
}

$data = explode('|', $dataString);

$filename = __DIR__ . '/' . $dataString . '.png';

if(file_exists($filename)) {
	header('Location: ' . $dataString . '.png');
	die;
}

$image = imageCreateTrueColor(600, 600);
imageSaveAlpha($image, true);
$trans_color = imageColorAllocateAlpha($image, 0, 0, 0, 127);
imageFill($image, 0, 0, $trans_color);

foreach($layerOrder as $layer) {
	$id = $data[$layer-1] = intVal($data[$layer-1]);
	if($id === 0) {
		continue;
	}
	$item_filename = __DIR__ . '/../paper_items/' . $id . '.png';
	if(!file_exists($item_filename)) {
		try {
			saveItem($id);
		} catch(Exception $e) {
			$data[$layer-1] = 0;
		}
	}
	$newData = implode('|', $data);
	if($newData !== $dataString) {
		header('Location: ' . $newData . '.png');
		die;
	}

	$item_image = imageCreateFromPng($item_filename);
	imageAlphaBlending($item_image, false);
	imageSaveAlpha($item_image, true);
	imageCopy($image, $item_image, 0, 0, 0, 0, 600, 600);
}

imagePng($image, $filename);
header('Location: ' . $dataString . '.png');
