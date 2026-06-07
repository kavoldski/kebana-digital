<?php
require_once 'bootstrap.php';

$route = $_GET['route'] ?? 'portal';
$route = preg_replace('/\.php$/', '', $route);

// Define public routes that don't require authentication
$public_routes = ['login', 'authenticate', 'sign_up', 'register', 'portal', 'events/checkin', 'test_user_dashboard', 'mobile-ocr', 'api/ocr/upload_image', 'manual'];

$is_public = in_array($route, $public_routes) || preg_match('/^portal\/view\/\d+$/', $route);

if (!$is_public) {
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
    'events/checkin' => 'modules/events/checkin.php',
    'events/gantt' => 'modules/events/gantt.php',
    'members' => 'modules/members/list.php',
    'members/add' => 'modules/members/add.php',
    'members/report' => 'modules/members/report.php',
    'finance' => 'modules/finance/dashboard.php',
    'finance/transactions/list' => 'modules/finance/transactions/list.php',
    'finance/transactions/create' => 'modules/finance/transactions/create.php',
    'finance/transactions/edit' => 'modules/finance/transactions/edit.php',
    'finance/transactions/delete' => 'modules/finance/transactions/delete.php',
    'finance/transactions/generate' => 'modules/finance/transactions/generate.php',
    'finance/budget' => 'modules/finance/budget.php',
    'documents' => 'modules/documents/index.php',
    'documents/upload' => 'modules/documents/upload.php',
    'chat' => 'modules/chat/index.php',
    'audit' => 'modules/audit/list.php',
    'users' => 'modules/users/list.php',
    'users/add' => 'modules/users/add.php',
    'notifications' => 'modules/notifications/index.php',
    'announcements' => 'modules/announcements/index.php',
    'announcements/index' => 'modules/announcements/index.php',
    'announcements/create' => 'modules/announcements/create.php',
    'api/generate_ai' => 'modules/api/generate_ai.php',
    'announcements/delete_image' => 'modules/announcements/delete_image.php',
    'portal' => 'modules/portal/index.php',
    'mobile-ocr' => 'modules/members/mobile-ocr.php',
    'api/ocr/generate_session' => 'modules/api/ocr/generate_session.php',
    'api/ocr/upload_image' => 'modules/api/ocr/upload_image.php',
    'api/ocr/check_session' => 'modules/api/ocr/check_session.php',
    'manual' => 'modules/manual/index.php',
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

if (preg_match('/^announcements\/edit\/(\d+)$/', $route, $matches)) {
    $_GET['id'] = $matches[1];
    require_once 'modules/announcements/edit.php';
    exit();
}

if (preg_match('/^portal\/view\/(\d+)$/', $route, $matches)) {
    $_GET['id'] = $matches[1];
    require_once 'modules/portal/view.php';
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
