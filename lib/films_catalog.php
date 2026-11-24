<?php
declare(strict_types=1);

require_once __DIR__ . '/custom_films_store.php';

/**
 * Built-in films only (unchanged).
 *
 * @return array<string, array{filmId:string,title:string,price:int,views:int,img:string,href:string,genre:string}>
 */
function films_catalog_static(): array
{
    return [
        'film1' => ['filmId' => 'film1', 'title' => 'Film 1', 'price' => 299, 'views' => 1461, 'img' => 'assets/images/film 1_photo-resizer.ru.jpg', 'href' => '/film1.php', 'genre' => 'action'],
        'film2' => ['filmId' => 'film2', 'title' => 'Film 2', 'price' => 319, 'views' => 1703, 'img' => 'assets/images/film 2_photo-resizer.ru.jpg', 'href' => '/film2.php', 'genre' => 'action'],
        'film3' => ['filmId' => 'film3', 'title' => 'Film 3', 'price' => 249, 'views' => 1387, 'img' => 'assets/images/film 3_photo-resizer.ru.jpg', 'href' => '/film3.php', 'genre' => 'action'],
        'film4' => ['filmId' => 'film4', 'title' => 'Film 4', 'price' => 279, 'views' => 1514, 'img' => 'assets/images/film 4_photo-resizer.ru.jpg', 'href' => '/film4.php', 'genre' => 'action'],
        'film5' => ['filmId' => 'film5', 'title' => 'Film 5', 'price' => 249, 'views' => 1278, 'img' => 'assets/images/film 5_photo-resizer.ru.jpg', 'href' => '/film5.php', 'genre' => 'action'],
        'film6' => ['filmId' => 'film6', 'title' => 'Film 6', 'price' => 179, 'views' => 542, 'img' => 'assets/images/film 6_photo-resizer.ru.jpg', 'href' => '/film6.php', 'genre' => 'drama'],
        'film7' => ['filmId' => 'film7', 'title' => 'Film 7', 'price' => 299, 'views' => 1189, 'img' => 'assets/images/film 7_photo-resizer.ru.jpg', 'href' => '/film7.php', 'genre' => 'drama'],
        'film8' => ['filmId' => 'film8', 'title' => 'Film 8', 'price' => 229, 'views' => 813, 'img' => 'assets/images/film 8_photo-resizer.ru.jpg', 'href' => '/film8.php', 'genre' => 'drama'],
        'film9' => ['filmId' => 'film9', 'title' => 'Film 9', 'price' => 399, 'views' => 1492, 'img' => 'assets/images/film 9_photo-resizer.ru.jpg', 'href' => '/film9.php', 'genre' => 'drama'],
        'film10' => ['filmId' => 'film10', 'title' => 'Film 10', 'price' => 489, 'views' => 1357, 'img' => 'assets/images/film 10_photo-resizer.ru.jpg', 'href' => '/film10.php', 'genre' => 'drama'],
        'film11' => ['filmId' => 'film11', 'title' => 'Film 11', 'price' => 269, 'views' => 921, 'img' => 'assets/images/film 11_photo-resizer.ru.jpg', 'href' => '/film11.php', 'genre' => 'comedy'],
        'film12' => ['filmId' => 'film12', 'title' => 'Film 12', 'price' => 199, 'views' => 654, 'img' => 'assets/images/film 12_photo-resizer.ru.jpg', 'href' => '/film12.php', 'genre' => 'comedy'],
        'film13' => ['filmId' => 'film13', 'title' => 'Film 13', 'price' => 449, 'views' => 1411, 'img' => 'assets/images/film 13_photo-resizer.ru.jpg', 'href' => '/film13.php', 'genre' => 'comedy'],
        'film14' => ['filmId' => 'film14', 'title' => 'Film 14', 'price' => 319, 'views' => 1053, 'img' => 'assets/images/film 14_photo-resizer.ru.jpg', 'href' => '/film14.php', 'genre' => 'comedy'],
        'film15' => ['filmId' => 'film15', 'title' => 'Film 15', 'price' => 429, 'views' => 1302, 'img' => 'assets/images/film 15_photo-resizer.ru.jpg', 'href' => '/film15.php', 'genre' => 'comedy'],
        'film16' => ['filmId' => 'film16', 'title' => 'Film 16', 'price' => 359, 'views' => 1175, 'img' => 'assets/images/film 16_photo-resizer.ru.jpg', 'href' => '/film16.php', 'genre' => 'fantasy'],
        'film17' => ['filmId' => 'film17', 'title' => 'Film 17', 'price' => 239, 'views' => 951, 'img' => 'assets/images/film 17_photo-resizer.ru.jpg', 'href' => '/film17.php', 'genre' => 'fantasy'],
        'film18' => ['filmId' => 'film18', 'title' => 'Film 18', 'price' => 469, 'views' => 1468, 'img' => 'assets/images/film 18_photo-resizer.ru.jpg', 'href' => '/film18.php', 'genre' => 'fantasy'],
        'film19' => ['filmId' => 'film19', 'title' => 'Film 19', 'price' => 289, 'views' => 1089, 'img' => 'assets/images/film 19_photo-resizer.ru.jpg', 'href' => '/film19.php', 'genre' => 'fantasy'],
        'film20' => ['filmId' => 'film20', 'title' => 'Film 20', 'price' => 379, 'views' => 1221, 'img' => 'assets/images/film 20_photo-resizer.ru.jpg', 'href' => '/film20.php', 'genre' => 'fantasy'],
    ];
}

/**
 * Static catalog + admin-added films from JSON.
 *
 * @return array<string, array{filmId:string,title:string,price:int,views:int,img:string,href:string,genre:string,description?:string}>
 */
function films_catalog(): array
{
    $base = films_catalog_static();
    foreach (custom_films_as_catalog_map() as $id => $row) {
        if (!isset($base[$id])) {
            $base[$id] = $row;
        }
    }

    return $base;
}
