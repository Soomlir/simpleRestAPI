# simpleRestAPI

Простой REST API

Что нужно делать:

Создать API для управления списком задач (To-Do List).

Реализовать основные методы:
- GET /tasks - получить все задачи.
- POST /tasks - создать новую задачу.
- PUT /tasks/{id} - обновить задачу.
- DELETE /tasks/{id} - удалить задачу.

Данные передавать в формате JSON.


## Как использовать этот API:

1. Получить все задачи
```php
GET /tasks
```

2. Получить одну задачу
```php
GET /tasks/1
```

3. Создать новую задачу
```php
POST /tasks
Content-Type: application/json

{
    "title": "Купить продукты",
    "description": "Молоко, хлеб, яйца"
}
```

4. Обновить задачу
```php
PUT /tasks/1
Content-Type: application/json

{
    "title": "Купить продукты обновлено",
    "completed": true
}
```

5. Удалить задачу
```php
DELETE /tasks/1
```

## Особенности реализации:
- Хранение данных: Используется JSON файл tasks.json
- CORS поддержка: Можно использовать с фронтендом на другом домене
- Валидация: Проверка обязательных полей
- Обработка ошибок: Соответствующие HTTP статусы
- Автогенерация ID: Автоматическая нумерация задач
- Метка времени: created_at и updated_at для каждой задачи

## Для тестирования можно использовать curl:
```bash
# Создать задачу
curl -X POST http://yourserver/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":"Тестовая задача","description":"Описание"}'

# Получить все задачи
curl http://yourserver/tasks

# Обновить задачу
curl -X PUT http://yourserver/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{"title":"Обновленная задача","completed":true}'

# Удалить задачу
curl -X DELETE http://yourserver/tasks/1
```

API готов к использованию и полностью соответствует требованиям REST!

Должны работать ВСЕ endpoints:
- Все задачи - GET http://simplerestapi/index.php/tasks
- Одна задача - GET http://simplerestapi/index.php/tasks/1
- Создать - POST http://simplerestapi/index.php/tasks
- Обновить - PUT http://simplerestapi/index.php/tasks/1
- Удалить - DELETE http://simplerestapi/index.php/tasks/1
