### Event management system (api)
___
Демонстрация реализации API, авторизации в API через токены,
запуск обработки событий через очереди обработки сообщений,
валидации Request запросов.

Стек:
- 🐘 Php 8.2 + Laravel 10 (авторизация на базе API токенов)
- 🌌 Swagger - для документации API + Swagger UI
- 🦖 MariaDb
- 🐳 Docker (Docker compose) + Laravel Sail
- ⛑ Phpunit - тестирование.

### Настройка проекта и подготовка к старту docker

Настроить переменные окружения (если требуется изменить их):

```shell
cp .env.example .env
```

⚠ Если на машине разработчика установлен **php** и **composer** то можно выполнить команду:

```shell
composer install --ignore-platform-reqs
```

⚠ Если не установлен **php** и **composer** на машине разработчика то установить зависимости проекта можно так:

```shell
docker run --rm -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

на этом подготовка к работе с Laravel Sail закончена.

### Запуск проекта

Поднять docker контейнеры с помощью Laravel Sail
```shell
./vendor/bin/sail up -d
```

1.  Сгенерировать application key

```shell
./vendor/bin/sail artisan key:generate
```

2. Выполнить миграции и заполнить таблицы тестовыми данными

```shell
./vendor/bin/sail artisan migrate --seed
```
3. Запустить воркер (worker) обрабатывающий задачи из очереди сообщений
```shell
./vendor/bin/sail artisan queue:work --queue=default,user-notify
```
4. Для демонстрации работы команд выполняемых через **cron**
можно запустить команду в локальной среде разработки
    
```shell
./vendor/bin/sail artisan schedule:test --name=app:send-event-reminders
```
### Доступные сайты в dev окружении

|                Host                | Назначение                                                   |
|:----------------------------------:|:-------------------------------------------------------------|
|        http://localhost/api        | API приложения                                               |
| http://localhost/api/documentation | Swagger UI - документация к API формата Swagger 3.0          |
|       http://localhost:8025        | Mailpit - вэб интерфейс для отладки отправки email сообщения |
