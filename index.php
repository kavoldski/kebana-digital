<?php
require_once 'bootstrap.php';

$route = $_GET['route'] ?? 'dashboard';
$route = preg_replace('/\.php$/', '', $route);

// Define public routes that don't require authentication
$public_routes = ['login', 'authenticate', 'sign_up', 'register'];

if (!in_array($route, $public_routes)) {
    require_once 'includes/auth.php';
}

// Route mapping
$routes = [
    'dashboard' => 'src/php/index.php',
    'login' => 'modules/auth/login.php',
    'authenticate' => 'modules/auth/authenticate.php',
    'logout' => 'modules/auth/logout.php',
    'sign_up' => 'modules/auth/sign_up.php',
    'register' => 'modules/auth/register.php',
    'events' => 'modules/events/list.php',
    'events/create' => 'modules/events/create.php',
    'events/attendance' => 'modules/events/attendance.php',
    'events/gantt' => 'modules/events/gantt.php',
    'members' => 'modules/members/list.php',
    'members/add' => 'modules/members/add.php',
    'members/report' => 'modules/members/report.php',
    'finance' => 'modules/finance/dashboard.php',
    'finance/transactions/list' => 'modules/finance/transactions/list.php',
    'finance/transactions/create' => 'modules/finance/transactions/create.php',
    'finance/transactions/edit' => 'modules/finance/transactions/edit.php',
    'finance/transactions/delete' => 'modules/finance/transactions/delete.php',
    'finance/budget' => 'modules/finance/budget.php',
    'documents' => 'modules/documents/index.php',
    'documents/upload' => 'modules/documents/upload.php',
    'chat' => 'modules/chat/index.php',
    'audit' => 'modules/audit/list.php',
    'users' => 'modules/users/list.php',
    'users/add' => 'modules/users/add.php',
    'notifications' => 'modules/notifications/index.php',
];

// Clean up route (remove .php extension if present)
$route = preg_replace('/\.php$/', '', $route);

// Simple path matching
if (isset($routes[$route])) {
    require_once $routes[$route];
    exit();
}

// Dynamic route matching (e.g., members/view/5)
if (preg_match('/^members\/(view|edit)\/(\d+)$/', $route, $matches)) {
    $_GET['id'] = $matches[2];
    $action = $matches[1];
    require_once "modules/members/{$action}.php";
    exit();
}

if (preg_match('/^events\/(view|edit|attendance)\/(\d+)$/', $route, $matches)) {
    $_GET['id'] = $matches[2];
    $action = $matches[1];
    require_once "modules/events/{$action}.php";
    exit();
}

if (preg_match('/^finance\/event\/(\d+)$/', $route, $matches)) {
    $_GET['id'] = $matches[1];
    require_once "modules/finance/event.php";
    exit();
}

if (preg_match('/^users\/edit\/(\d+)$/', $route, $matches)) {
    $_GET['id'] = $matches[1];
    require_once "modules/users/edit.php";
    exit();
}

if ($route === 'cawangan') {
    require_once 'modules/cawangan/list.php';
    exit();
}

if ($route === 'cawangan/add') {
    require_once 'modules/cawangan/add.php';
    exit();
}

if (preg_match('/^cawangan\/edit\/(\d+)$/', $route, $matches)) {
    $_GET['id'] = $matches[1];
    require_once 'modules/cawangan/edit.php';
    exit();
}

// Fallback logic
if (file_exists($route . '.php')) {
    require_once $route . '.php';
} else {
    // Log unauthorized or missing route
    require_once 'src/php/index.php';
}
