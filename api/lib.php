<?php
session_start();

const DATA_DIR = __DIR__ . '/../data';
const UPLOAD_DIR = __DIR__ . '/../uploads';

function ensureDataFiles(): void {
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0777, true);
    }
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }

    $usersFile = DATA_DIR . '/users.json';
    if (!file_exists($usersFile)) {
        $admin = [
            'id' => 'u-admin-1',
            'name' => 'Primary Admin',
            'email' => 'admin@neoatelier.local',
            'password' => password_hash('Admin@123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'notify' => false,
            'createdAt' => gmdate('c')
        ];
        file_put_contents($usersFile, json_encode([$admin], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL, LOCK_EX);
    }

    $postsFile = DATA_DIR . '/posts.json';
    if (!file_exists($postsFile)) {
        $seed = [[
            'id' => 'seed-post-1',
            'title' => 'Building a chromatic memory map',
            'category' => 'Process',
            'cover' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=1400&q=80',
            'content' => '<p>I started from field recordings and converted frequencies into hand-painted color bars.</p>',
            'authorId' => 'u-admin-1',
            'authorName' => 'Primary Admin',
            'createdAt' => gmdate('c'),
            'likes' => 0,
            'dislikes' => 0
        ]];
        file_put_contents($postsFile, json_encode($seed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL, LOCK_EX);
    }

    $galleryFile = DATA_DIR . '/gallery.json';
    if (!file_exists($galleryFile)) {
        $seedGallery = [[
            'id' => 'g-1',
            'title' => 'Signal Bloom',
            'image' => 'https://images.unsplash.com/photo-1547891654-e66ed7ebb968?auto=format&fit=crop&w=1200&q=80',
            'createdAt' => gmdate('c')
        ]];
        file_put_contents($galleryFile, json_encode($seedGallery, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL, LOCK_EX);
    }

    $mailLog = DATA_DIR . '/mail-log.txt';
    if (!file_exists($mailLog)) {
        file_put_contents($mailLog, '');
    }
}

function jsonResponse($payload, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function readJsonFile(string $name): array {
    $path = DATA_DIR . '/' . $name;
    if (!file_exists($path)) {
        return [];
    }
    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function writeJsonFile(string $name, array $data): bool {
    $path = DATA_DIR . '/' . $name;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }
    return file_put_contents($path, $json . PHP_EOL, LOCK_EX) !== false;
}

function requestJson(): array {
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw ?: '{}', true);
    return is_array($decoded) ? $decoded : [];
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function requireRole(array $roles): array {
    $user = currentUser();
    if (!$user) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
    if (!in_array($user['role'], $roles, true)) {
        jsonResponse(['error' => 'Forbidden'], 403);
    }
    return $user;
}

function canPostRole(string $role): bool {
    return in_array($role, ['admin', 'subadmin'], true);
}

function logMail(string $to, string $subject, string $body): void {
    $line = sprintf("[%s] TO:%s | %s | %s\n", gmdate('c'), $to, $subject, $body);
    file_put_contents(DATA_DIR . '/mail-log.txt', $line, FILE_APPEND | LOCK_EX);
}

ensureDataFiles();
