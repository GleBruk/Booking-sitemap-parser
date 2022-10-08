<?php
//url с картами сайта
$robotsTxtUrl = 'https://www.booking.com/robots.txt';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $robotsTxtUrl);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.54 Safari/537.36');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$content = curl_exec($ch);
curl_close($ch);

//Получаем xml со ссылками
preg_match_all('~(https:.*\.xml)\n~', $content, $a);
$urls = $a[1];
//print_r($urls);

//Получаем ссылки на файлы
$xml = simplexml_load_file($urls[0]);
$sitemaps = [];
foreach ($xml->sitemap as $sitemap){
    $sitemaps[] = $sitemap->loc;
}

//Берём ссылку на первый попавшийся файл и получаем его название, т.к. нет цели спарсить всю карту
preg_match('~https://www.booking.com/(.*)~', $sitemaps[0], $a);
$file_name = 'sitemap\\' . $a[1];


//Создаём файл, куда сохраним тот файл, который мы будем загружать
$fp = fopen('C:\Users\GlebRu\Downloads\OSPanel\domains\booking-sitemap-parser\\' . $file_name, "w");
$ch1 = curl_init();
//Делаем паузу перед новым запросом по ссылке
sleep(3);
curl_setopt($ch1, CURLOPT_URL, $sitemaps[0]);
curl_setopt($ch1, CURLOPT_ENCODING, 'gzip, deflate');
curl_setopt($ch1, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.54 Safari/537.36');
//Указываем созданный файл
curl_setopt($ch1, CURLOPT_FILE, $fp);
curl_exec($ch1);
curl_close($ch1);
fclose($fp);

//Т.к. файл будет архивом, то его нужно прочесть и записать новый xml файл
$buffer_size =12096;
//Название для нового файла
$out_file_name = str_replace('.gz', '', $file_name);
//Открываем файлы
$file = gzopen($file_name, 'rb');
$out_file = fopen($out_file_name, 'wb');
//Читаем архив и записываем xml файл
while (!gzeof($file)) {
    fwrite($out_file, gzread($file, $buffer_size));
}
//Закрываем файлы
fclose($out_file);
gzclose($file);

//Получаем ссылки из xml файла
$xml = simplexml_load_file($out_file_name);
$links = [];
foreach ($xml->url as $url){
    $links[] = $url->loc;
}

//Выводим ссылки
foreach ($links as $link){
    echo $link . "<br/>";
}