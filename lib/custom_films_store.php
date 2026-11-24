<?php
declare(strict_types=1);

function custom_films_file(): string
{
    return dirname(__DIR__) . '/data/catalog/custom_films.json';
}

function custom_films_ensure(): void
{
    $dir = dirname(custom_films_file());
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $path = custom_films_file();
    if (!is_file($path)) {
        file_put_contents($path, "[]\n", LOCK_EX);
    }
}

/** @return list<array<string,mixed>> */
function custom_films_load_raw(): array
{
    custom_films_ensure();
    $raw = @file_get_contents(custom_films_file());
    if ($raw === false || $raw === '') {
        return [];
    }
    $data = json_decode($raw, true);

    return is_array($data) ? $data : [];
}

/**
 * Persist full list (internal).
 *
 * @param list<array<string,mixed>> $rows
 */
function custom_films_save_raw(array $rows): bool
{
    custom_films_ensure();
    $json = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    return $json !== false && file_put_contents(custom_films_file(), $json . "\n", LOCK_EX) !== false;
}

function custom_film_id_valid(string $id): bool
{
    return (bool) preg_match('/^cf_[a-f0-9]{16}$/', $id);
}

/**
 * @return array<string, array{filmId:string,title:string,price:int,views:int,img:string,href:string,genre:string,description:string}>
 */
function custom_films_as_catalog_map(): array
{
    $out = [];
    foreach (custom_films_load_raw() as $row) {
        if (!is_array($row)) {
            continue;
        }
        $id = (string) ($row['filmId'] ?? '');
        if ($id === '' || !custom_film_id_valid($id)) {
            continue;
        }
        $title = trim((string) ($row['title'] ?? ''));
        if ($title === '') {
            continue;
        }
        $g = (string) ($row['genre'] ?? 'action');
        if (!in_array($g, ['action', 'drama', 'comedy', 'fantasy'], true)) {
            $g = 'action';
        }
        $price = (int) ($row['price'] ?? 0);
        if ($price < 0) {
            $price = 0;
        }
        if ($price > 9999999) {
            $price = 9999999;
        }
        $desc = (string) ($row['description'] ?? '');
        if (strlen($desc) > 12000) {
            $desc = substr($desc, 0, 12000);
        }
        $out[$id] = [
            'filmId' => $id,
            'title' => $title,
            'price' => $price,
            'views' => 0,
            'img' => '',
            'href' => '/film.php?id=' . rawurlencode($id),
            'genre' => $g,
            'description' => $desc,
        ];
    }

    return $out;
}

function custom_films_append(string $title, int $price, string $description, string $genre): ?string
{
    $title = trim($title);
    if ($title === '' || strlen($title) > 200) {
        return null;
    }
    if (!in_array($genre, ['action', 'drama', 'comedy', 'fantasy'], true)) {
        $genre = 'action';
    }
    if ($price < 0) {
        $price = 0;
    }
    if ($price > 9999999) {
        $price = 9999999;
    }
    if (strlen($description) > 12000) {
        $description = substr($description, 0, 12000);
    }
    $id = 'cf_' . bin2hex(random_bytes(8));
    $rows = custom_films_load_raw();
    $rows[] = [
        'filmId' => $id,
        'title' => $title,
        'price' => $price,
        'description' => $description,
        'genre' => $genre,
        'createdAt' => time(),
    ];
    if (!custom_films_save_raw($rows)) {
        return null;
    }

    return $id;
}

/** Remove one admin-added film by id. Built-in film1…film20 cannot be removed here. */
function custom_films_remove(string $filmId): bool
{
    if (!custom_film_id_valid($filmId)) {
        return false;
    }
    $rows = custom_films_load_raw();
    $next = [];
    $removed = false;
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $id = (string) ($row['filmId'] ?? '');
        if ($id === $filmId) {
            $removed = true;
            continue;
        }
        $next[] = $row;
    }
    if (!$removed) {
        return false;
    }

    return custom_films_save_raw($next);
}

/** Update one admin-added film by id. */
function custom_films_update(string $filmId, string $title, int $price, string $description, string $genre): bool
{
    if (!custom_film_id_valid($filmId)) {
        return false;
    }
    $title = trim($title);
    if ($title === '' || strlen($title) > 200) {
        return false;
    }
    if (!in_array($genre, ['action', 'drama', 'comedy', 'fantasy'], true)) {
        $genre = 'action';
    }
    if ($price < 0) {
        $price = 0;
    }
    if ($price > 9999999) {
        $price = 9999999;
    }
    if (strlen($description) > 12000) {
        $description = substr($description, 0, 12000);
    }
    $rows = custom_films_load_raw();
    $updated = false;
    foreach ($rows as &$row) {
        if (!is_array($row)) {
            continue;
        }
        $id = (string) ($row['filmId'] ?? '');
        if ($id === $filmId) {
            $row['title'] = $title;
            $row['price'] = $price;
            $row['description'] = $description;
            $row['genre'] = $genre;
            $updated = true;
            break;
        }
    }
    if (!$updated) {
        return false;
    }

    return custom_films_save_raw($rows);
}
