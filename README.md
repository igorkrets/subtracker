# SubTracker

Трекер подписок, серверов и доменов с Telegram-уведомлениями и REST API.

**Демо**: https://sub.syspage.ru

---

## Что делает

SubTracker помогает не забыть продлить VPS, домен, SSL, SaaS-подписку или любой другой сервис. Вместо таблиц — удобный интерфейс, группировка, уведомления в Telegram и полный REST API для автоматизации.

### Возможности

- Учёт сервисов: дата истечения, стоимость (в оригинальной валюте), IP, URL, заметки
- Telegram-уведомления за 1/3/7/14/30 дней до истечения
- Продление сервисов прямо из Telegram (inline-кнопки)
- Группировка с drag-and-drop, цветовые метки, иконки
- Каталог 85+ провайдеров (Hetzner, DigitalOcean, Cloudflare, Timeweb и др.)
- Зашифрованные заметки (AES-256-GCM, ключ только у пользователя)
- REST API v1 с документацией и интерактивным тестированием прямо в браузере
- Экспорт: XLSX, PDF, HTML, JSON-бекап / импорт
- Вебхуки и настройка уведомлений по группам
- Тёмная тема, адаптивный дизайн, PWA

---

## Требования

| | |
|---|---|
| PHP | 8.3+ |
| Node.js | 20+ |
| Composer | 2 |
| База данных | SQLite (по умолчанию), MySQL 8+ или PostgreSQL 15+ |

---

## Установка — обычный сервер

### 1. Клонирование и зависимости

```bash
git clone https://github.com/yourname/subtracker.git /var/www/subtracker
cd /var/www/subtracker

cp .env.example .env
nano .env  # Заполните APP_URL, DB_* и другие параметры

composer install --optimize-autoloader --no-dev
npm ci && npm run build
```

### 2. Инициализация

```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize

# Права для PHP-FPM (www-data)
chown -R www-data:www-data storage bootstrap/cache
```

### 3. Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/subtracker/public;
    index index.php;

    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 60;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Для HTTPS добавьте certbot: `certbot --nginx -d yourdomain.com`

### 4. Планировщик (cron)

Добавьте от имени www-data:

```bash
crontab -u www-data -e
```

```
* * * * * php /var/www/subtracker/artisan schedule:run >> /dev/null 2>&1
```

### 5. Telegram-бот

```bash
# Установить вебхук (выполнять на сервере после настройки HTTPS)
php artisan telegram:webhook set

# Удалить вебхук
php artisan telegram:webhook delete
```

---

## Установка — Docker Compose

### Быстрый старт

```bash
git clone https://github.com/yourname/subtracker.git
cd subtracker

cp .env.example .env
# Укажите APP_URL=http://localhost:8080 (или ваш домен)

docker compose up -d --build

docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize
```

Приложение доступно на `http://localhost:8080`.

Файлы хранятся в `./storage` и `./database` — смонтированы как volumes.

### Webhook для Telegram через Docker

```bash
docker compose exec app php artisan telegram:webhook set
```

---

## Переменные окружения (.env)

```dotenv
APP_NAME=SubTracker
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=                          # php artisan key:generate

# База данных SQLite (по умолчанию)
DB_CONNECTION=sqlite
# DB_DATABASE=/var/www/subtracker/database/database.sqlite

# Или MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=subtracker
# DB_USERNAME=subtracker
# DB_PASSWORD=secret

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Telegram-бот (опционально — для уведомлений)
TELEGRAM_BOT_TOKEN=123456789:ABC-DEFghijklmno
TELEGRAM_WEBHOOK_SECRET=случайная_строка_32_символа

# Контакты в подвале лендинга
CONTACT_EMAIL=admin@example.com
CONTACT_TG=@yourhandle
```

---

## Первый вход

После установки перейдите на `APP_URL` и зарегистрируйтесь. Первый пользователь автоматически получает доступ к `/admin`.

---

## Обновление

```bash
git pull
composer install --optimize-autoloader --no-dev
npm ci && npm run build
php artisan migrate --force
php artisan optimize
chown -R www-data:www-data storage bootstrap/cache
```

---

## Стек

Laravel 13 · PHP 8.3 · Alpine.js 3 · Tailwind CSS 4 · SQLite/MySQL · Vite
