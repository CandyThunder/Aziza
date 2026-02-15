<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$dataFile = __DIR__ . '/../data/posts.json';

function readPosts(string $dataFile): array {
    if (!file_exists($dataFile)) {
        return [];
    }

    $raw = file_get_contents($dataFile);
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function writePosts(string $dataFile, array $posts): bool {
    $payload = json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($payload === false) {
        return false;
    }
    return file_put_contents($dataFile, $payload . PHP_EOL, LOCK_EX) !== false;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $posts = readPosts($dataFile);
    usort($posts, fn($a, $b) => strtotime($b['createdAt'] ?? '') <=> strtotime($a['createdAt'] ?? ''));
    echo json_encode($posts);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input') ?: '{}', true);

    $required = ['title', 'category', 'image', 'content'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || trim((string)$input[$field]) === '') {
            http_response_code(422);
            echo json_encode(['error' => "Missing field: {$field}"]);
            exit;
        }
    }

    $posts = readPosts($dataFile);
    $newPost = [
        'id' => uniqid('post-', true),
        'title' => trim((string)$input['title']),
        'category' => trim((string)$input['category']),
        'image' => trim((string)$input['image']),
        'content' => trim((string)$input['content']),
        'createdAt' => gmdate('c')
    ];

    $posts[] = $newPost;

    if (!writePosts($dataFile, $posts)) {
        http_response_code(500);
        echo json_encode(['error' => 'Could not save post']);
        exit;
    }

    http_response_code(201);
    echo json_encode($newPost);
    exit;
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? '';
    if ($id === '') {
        http_response_code(422);
        echo json_encode(['error' => 'Missing id']);
        exit;
    }

    $posts = readPosts($dataFile);
    $before = count($posts);
    $posts = array_values(array_filter($posts, fn($post) => ($post['id'] ?? '') !== $id));

    if ($before === count($posts)) {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found']);
        exit;
    }

    if (!writePosts($dataFile, $posts)) {
        http_response_code(500);
        echo json_encode(['error' => 'Could not delete post']);
        exit;
    }

    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
