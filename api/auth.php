<?php
require_once __DIR__ . '/lib.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'status';

if ($method === 'GET' && $action === 'status') {
    jsonResponse(['user' => currentUser()]);
}

if ($method === 'POST' && $action === 'register') {
    $input = requestJson();
    foreach (['name', 'email', 'password'] as $field) {
        if (empty(trim((string)($input[$field] ?? '')))) {
            jsonResponse(['error' => "Missing {$field}"], 422);
        }
    }

    $users = readJsonFile('users.json');
    $email = strtolower(trim((string)$input['email']));
    foreach ($users as $user) {
        if (strtolower($user['email']) === $email) {
            jsonResponse(['error' => 'Email already in use'], 409);
        }
    }

    $newUser = [
        'id' => uniqid('u-', true),
        'name' => trim((string)$input['name']),
        'email' => $email,
        'password' => password_hash((string)$input['password'], PASSWORD_DEFAULT),
        'role' => 'user',
        'notify' => true,
        'createdAt' => gmdate('c')
    ];
    $users[] = $newUser;
    writeJsonFile('users.json', $users);

    $_SESSION['user'] = [
        'id' => $newUser['id'],
        'name' => $newUser['name'],
        'email' => $newUser['email'],
        'role' => $newUser['role']
    ];

    jsonResponse(['user' => $_SESSION['user']], 201);
}

if ($method === 'POST' && $action === 'login') {
    $input = requestJson();
    $email = strtolower(trim((string)($input['email'] ?? '')));
    $password = (string)($input['password'] ?? '');

    $users = readJsonFile('users.json');
    $found = null;
    foreach ($users as $user) {
        if (strtolower($user['email']) === $email) {
            $found = $user;
            break;
        }
    }

    if (!$found || !password_verify($password, $found['password'])) {
        jsonResponse(['error' => 'Invalid credentials'], 401);
    }

    $_SESSION['user'] = [
        'id' => $found['id'],
        'name' => $found['name'],
        'email' => $found['email'],
        'role' => $found['role']
    ];

    jsonResponse(['user' => $_SESSION['user'], 'isAdminSeed' => $found['email'] === 'admin@neoatelier.local']);
}

if ($method === 'POST' && $action === 'logout') {
    $_SESSION = [];
    session_destroy();
    jsonResponse(['ok' => true]);
}

if ($method === 'POST' && $action === 'invite-subadmin') {
    $actor = requireRole(['admin']);
    $input = requestJson();
    foreach (['name', 'email', 'password'] as $field) {
        if (empty(trim((string)($input[$field] ?? '')))) {
            jsonResponse(['error' => "Missing {$field}"], 422);
        }
    }

    $users = readJsonFile('users.json');
    $email = strtolower(trim((string)$input['email']));
    foreach ($users as $u) {
        if (strtolower($u['email']) === $email) {
            jsonResponse(['error' => 'Email already exists'], 409);
        }
    }

    $subAdmin = [
        'id' => uniqid('u-', true),
        'name' => trim((string)$input['name']),
        'email' => $email,
        'password' => password_hash((string)$input['password'], PASSWORD_DEFAULT),
        'role' => 'subadmin',
        'notify' => false,
        'createdAt' => gmdate('c'),
        'invitedBy' => $actor['id']
    ];
    $users[] = $subAdmin;
    writeJsonFile('users.json', $users);

    logMail($subAdmin['email'], 'Subadmin invite', 'You were added as a subadmin.');
    jsonResponse(['ok' => true, 'subadmin' => ['email' => $subAdmin['email'], 'role' => $subAdmin['role']]], 201);
}

jsonResponse(['error' => 'Not found'], 404);
