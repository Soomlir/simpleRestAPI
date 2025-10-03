<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  exit();
}

define('DATA_FILE', 'tasks.json');

if (!file_exists(DATA_FILE)) {
  file_put_contents(DATA_FILE, json_encode([]));
}

function getTasks()
{
  $data = file_get_contents(DATA_FILE);
  return json_decode($data, true) ?: [];
}

function saveTasks($tasks)
{
  file_put_contents(DATA_FILE, json_encode($tasks, JSON_PRETTY_PRINT));
  return true;
}

function generateId($tasks)
{
  if (empty($tasks)) return 1;
  $ids = array_column($tasks, 'id');
  return empty($ids) ? 1 : max($ids) + 1;
}

// ПРОСТАЯ И НАДЕЖНАЯ МАРШРУТИЗАЦИЯ
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

// Получаем путь и убираем параметры
$path = parse_url($request_uri, PHP_URL_PATH);

// ДЕБАГ - посмотрим что приходит
error_log("=== NEW REQUEST ===");
error_log("Method: " . $method);
error_log("Full URI: " . $request_uri);
error_log("Path: " . $path);

// Разбираем путь
$clean_path = trim($path, '/');
$segments = $clean_path ? explode('/', $clean_path) : [];

// Убираем index.php из segments если он есть
if (isset($segments[0]) && $segments[0] === 'index.php') {
  array_shift($segments);
}

error_log("Clean segments: " . implode(', ', $segments));

// ОПРЕДЕЛЯЕМ ЧТО ЗАПРАШИВАЕТСЯ
$endpoint = $segments[0] ?? '';
$task_id = $segments[1] ?? null;

error_log("Endpoint: " . $endpoint);
error_log("Task ID: " . $task_id);

// ОБРАБОТКА ЗАПРОСОВ
try {
  // КОРНЕВОЙ URL - показать документацию
  if (empty($segments)) {
    echo json_encode([
      'message' => 'Simple REST API for To-Do List',
      'endpoints' => [
        'GET /tasks' => 'Get all tasks',
        'GET /tasks/{id}' => 'Get single task',
        'POST /tasks' => 'Create new task',
        'PUT /tasks/{id}' => 'Update task',
        'DELETE /tasks/{id}' => 'Delete task'
      ]
    ]);
    exit;
  }

  // ВСЕ ЗАДАЧИ
  if ($endpoint === 'tasks' && $task_id === null) {
    if ($method === 'GET') {
      // GET /tasks - все задачи
      echo json_encode(getTasks());
      exit;
    }

    if ($method === 'POST') {
      // POST /tasks - создать задачу
      $input = json_decode(file_get_contents('php://input'), true);

      if (empty(trim($input['title'] ?? ''))) {
        http_response_code(400);
        echo json_encode(['error' => 'Title is required']);
        exit;
      }

      $tasks = getTasks();
      $new_task = [
        'id' => generateId($tasks),
        'title' => trim($input['title']),
        'description' => trim($input['description'] ?? ''),
        'completed' => false,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
      ];

      $tasks[] = $new_task;
      saveTasks($tasks);

      http_response_code(201);
      echo json_encode($new_task);
      exit;
    }
  }

  // ОДНА ЗАДАЧА
  if ($endpoint === 'tasks' && $task_id !== null && is_numeric($task_id)) {
    $task_id = (int)$task_id;
    $tasks = getTasks();
    $task_index = null;

    // Находим задачу
    foreach ($tasks as $index => $task) {
      if ($task['id'] == $task_id) {
        $task_index = $index;
        break;
      }
    }

    if ($task_index === null) {
      http_response_code(404);
      echo json_encode(['error' => 'Task not found']);
      exit;
    }

    if ($method === 'GET') {
      // GET /tasks/{id} - получить одну задачу
      echo json_encode($tasks[$task_index]);
      exit;
    }

    if ($method === 'PUT') {
      // PUT /tasks/{id} - обновить задачу
      $input = json_decode(file_get_contents('php://input'), true);

      if (isset($input['title']) && !empty(trim($input['title']))) {
        $tasks[$task_index]['title'] = trim($input['title']);
      }
      if (isset($input['description'])) {
        $tasks[$task_index]['description'] = trim($input['description']);
      }
      if (isset($input['completed'])) {
        $tasks[$task_index]['completed'] = (bool)$input['completed'];
      }
      $tasks[$task_index]['updated_at'] = date('Y-m-d H:i:s');

      saveTasks($tasks);
      echo json_encode(['message' => 'Task updated successfully', 'task' => $tasks[$task_index]]);
      exit;
    }

    if ($method === 'DELETE') {
      // DELETE /tasks/{id} - удалить задачу
      array_splice($tasks, $task_index, 1);
      saveTasks($tasks);
      echo json_encode(['message' => 'Task deleted successfully']);
      exit;
    }
  }

  // ЕСЛИ ДОШЛИ СЮДА - КОНЕЧНАЯ ТОЧКА НЕ НАЙДЕНА
  http_response_code(404);
  echo json_encode([
    'error' => 'Endpoint not found',
    'requested_path' => $path,
    'segments' => $segments,
    'available_endpoints' => [
      'GET /tasks',
      'GET /tasks/{id}',
      'POST /tasks',
      'PUT /tasks/{id}',
      'DELETE /tasks/{id}'
    ]
  ]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
