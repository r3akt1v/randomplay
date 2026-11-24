# RandomPlay — видеопрокат / каталог фильмов

Веб-приложение видеопроката на PHP + MySQL: каталог фильмов, корзина,
покупки, отзывы и оценки, личный кабинет, админ-панель и REST API.

## Возможности

- **Каталог фильмов** — ~20 фильмов, страницы фильмов, жанры, цены
- **Корзина и покупки** — добавление в корзину (`cart.js`), оформление заказа
- **Авторизация** — регистрация, вход, личный кабинет (`user.php`)
- **Отзывы и оценки** — рейтинги фильмов, история оценок (`ratings_history.php`)
- **Админ-панель** (`admin.php`) — CRUD-управление кастомными фильмами:
  добавление, редактирование (модальное окно), удаление; данные хранятся
  в JSON (`data/catalog/custom_films.json`)
- **REST API** (`api/`) — JSON endpoints для истории покупок, просмотров
  и оценок (`history.php`, `track.php`, `analytics_json.php`)
- **Аналитика** — сбор статистики просмотров/оценок (`charts.php`,
  `report.php`, `lib/analytics_*.php`)
- **Экспорт в Excel** — `export_excel.php`

## Стек

- **Backend:** PHP (server-side рендеринг, сессии, cookies)
- **Database:** MySQL (`php-website`), часть данных — JSON-файлы в `data/`
- **Frontend:** HTML + CSS (`style.css`) + JavaScript (`cart.js`, `mashtab.js`, `perehod.js`, `weather.js`)
- **API:** REST, ответы в JSON, HTTP-статусы 200/400/401/403

## Структура

```
index.php, films.php, film*.php   — каталог и страницы фильмов
login.php, register.php, user.php — авторизация и ЛК
cart.js                            — корзина (клиентская логика)
admin.php                          — админ-панель (CRUD фильмов)
api/                               — REST API (history, track, analytics)
lib/                               — db.php, хранилища (JSON + MySQL)
data/                              — JSON-данные: пользователи, каталог, аналитика
database.sql                       — дамп MySQL (схема + тестовые данные)
blocks/, assets/                   — UI-компоненты и статика
ARCHITECTURE_REPORT.txt            — отчёт по архитектуре (курсовая)
DEV_NOTES.txt                      — заметки для разработчика
```

## Запуск локально

Требуется PHP 7.4+ и MySQL.

1. Создайте БД и залейте дамп:

```bash
mysql -u root -p -e "CREATE DATABASE php-website;"
mysql -u root -p php-website < database.sql
```

2. Поправьте подключение в `lib/db.php` под свои креды:

```php
$pdo = new PDO('mysql:host=localhost;dbname=php-website;charset=utf8mb4', 'root', 'ВАШ_ПАРОЛЬ');
```

3. Запустите сервер:

```bash
php -S localhost:8000
# или положите файлы в htdocs MAMP / var/www XAMPP
```

4. Откройте http://localhost:8000 — каталог фильмов. Админка: `admin.php`
   (логин `admin1`, пароль `admin` — тестовые, смените в БД).

## Тестовые данные

В `database.sql` есть демо-пользователи и один кастомный фильм.
История покупок/оценок хранится в `data/users/*.json`.

## Автор

Смирнов Александр — студент 4 курса, БГТУ «ВОЕНМЕХ» (ИСиТ)
