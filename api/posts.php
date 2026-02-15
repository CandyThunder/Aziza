<?php
require_once __DIR__ . '/lib.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    $posts = readJsonFile('posts.json');
    usort($posts, fn($a, $b) => strtotime($b['createdAt'] ?? '') <=> strtotime($a['createdAt'] ?? ''));
    jsonResponse($posts);
}

if ($method === 'POST' && $action === 'vote') {
    $input = requestJson();
    $postId = (string)($input['postId'] ?? '');
    $type = (string)($input['type'] ?? '');
    if (!in_array($type, ['like', 'dislike'], true) || $postId === '') {
        jsonResponse(['error' => 'Invalid vote payload'], 422);
    }

    $posts = readJsonFile('posts.json');
    $found = false;
    foreach ($posts as &$post) {
        if (($post['id'] ?? '') === $postId) {
            $found = true;
            $post[$type === 'like' ? 'likes' : 'dislikes'] = (int)($post[$type === 'like' ? 'likes' : 'dislikes'] ?? 0) + 1;
            break;
        }
    }

    if (!$found) {
        jsonResponse(['error' => 'Post not found'], 404);
    }

    writeJsonFile('posts.json', $posts);
    jsonResponse(['ok' => true]);
}

if ($method === 'POST') {
    $author = requireRole(['admin', 'subadmin']);

    $title = trim((string)($_POST['title'] ?? ''));
    $category = trim((string)($_POST['category'] ?? ''));
    $content = trim((string)($_POST['content'] ?? ''));
    $cover = trim((string)($_POST['cover'] ?? ''));

    if ($title === '' || $category === '' || $content === '') {
        jsonResponse(['error' => 'Missing required fields'], 422);
    }

    if (isset($_FILES['coverFile']) && $_FILES['coverFile']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['coverFile']['tmp_name'];
        $ext = pathinfo($_FILES['coverFile']['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $safeName = uniqid('cover-', true) . '.' . strtolower($ext);
        $dest = UPLOAD_DIR . '/' . $safeName;
        if (move_uploaded_file($tmp, $dest)) {
            $cover = 'uploads/' . $safeName;
        }
    }

    if ($cover === '') {
        jsonResponse(['error' => 'Provide cover image URL or file'], 422);
    }

    $posts = readJsonFile('posts.json');
    $newPost = [
        'id' => uniqid('post-', true),
        'title' => $title,
        'category' => $category,
        'cover' => $cover,
        'content' => $content,
        'authorId' => $author['id'],
        'authorName' => $author['name'],
        'createdAt' => gmdate('c'),
        'likes' => 0,
        'dislikes' => 0
    ];
    $posts[] = $newPost;
    writeJsonFile('posts.json', $posts);

    $users = readJsonFile('users.json');
    foreach ($users as $user) {
        if (($user['notify'] ?? false) && ($user['role'] ?? '') === 'user') {
            $subject = 'New post: ' . $newPost['title'];
            $message = 'A new studio post was published in category ' . $newPost['category'];
            @mail($user['email'], $subject, $message);
            logMail($user['email'], $subject, $message);
        }
    }

    jsonResponse($newPost, 201);
}

if ($method === 'DELETE') {
    requireRole(['admin', 'subadmin']);
    $id = (string)($_GET['id'] ?? '');
    if ($id === '') {
        jsonResponse(['error' => 'Missing id'], 422);
    }

    $posts = readJsonFile('posts.json');
    $before = count($posts);
    $posts = array_values(array_filter($posts, fn($p) => ($p['id'] ?? '') !== $id));
    if (count($posts) === $before) {
        jsonResponse(['error' => 'Post not found'], 404);
    }
    writeJsonFile('posts.json', $posts);
    jsonResponse(['ok' => true]);
}

jsonResponse(['error' => 'Method not allowed'], 405);
