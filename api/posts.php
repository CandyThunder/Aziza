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
    $user = currentUser();
    if (!$user) {
        jsonResponse(['error' => 'Login required to vote'], 401);
    }

    $input = requestJson();
    $postId = (string)($input['postId'] ?? '');
    $type = (string)($input['type'] ?? '');
    if (!in_array($type, ['like', 'dislike'], true) || $postId === '') {
        jsonResponse(['error' => 'Invalid vote payload'], 422);
    }

    $votes = readJsonFile('votes.json');
    $userVotes = $votes[$user['id']] ?? [];
    $previousType = $userVotes[$postId] ?? null;

    if ($previousType === $type) {
        jsonResponse(['ok' => true, 'message' => 'Vote unchanged']);
    }

    $posts = readJsonFile('posts.json');
    $found = false;
    foreach ($posts as &$post) {
        if (($post['id'] ?? '') === $postId) {
            $found = true;
            if ($previousType === 'like') {
                $post['likes'] = max(0, (int)($post['likes'] ?? 0) - 1);
            }
            if ($previousType === 'dislike') {
                $post['dislikes'] = max(0, (int)($post['dislikes'] ?? 0) - 1);
            }
            if ($type === 'like') {
                $post['likes'] = (int)($post['likes'] ?? 0) + 1;
            } else {
                $post['dislikes'] = (int)($post['dislikes'] ?? 0) + 1;
            }
            break;
        }
    }

    if (!$found) {
        jsonResponse(['error' => 'Post not found'], 404);
    }

    $votes[$user['id']][$postId] = $type;
    writeJsonFile('votes.json', $votes);
    writeJsonFile('posts.json', $posts);
    jsonResponse(['ok' => true]);
}

if ($method === 'POST') {
    $author = requireRole(['admin', 'subadmin']);

    $title = trim((string)($_POST['title'] ?? ''));
    $category = trim((string)($_POST['category'] ?? ''));
    $content = sanitizeHtml((string)($_POST['content'] ?? ''));
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

if ($method === 'PUT') {
    $editor = requireRole(['admin', 'subadmin']);
    $input = requestJson();
    $id = trim((string)($input['id'] ?? ''));
    if ($id === '') {
        jsonResponse(['error' => 'Missing id'], 422);
    }

    $posts = readJsonFile('posts.json');
    $updated = null;
    foreach ($posts as &$post) {
        if (($post['id'] ?? '') === $id) {
            if ($editor['role'] !== 'admin' && ($post['authorId'] ?? '') !== $editor['id']) {
                jsonResponse(['error' => 'You can edit only your own posts'], 403);
            }
            $post['title'] = trim((string)($input['title'] ?? $post['title']));
            $post['category'] = trim((string)($input['category'] ?? $post['category']));
            $content = sanitizeHtml((string)($input['content'] ?? $post['content']));
            $post['content'] = $content === '' ? $post['content'] : $content;
            $cover = trim((string)($input['cover'] ?? $post['cover']));
            $post['cover'] = $cover === '' ? $post['cover'] : $cover;
            $post['updatedAt'] = gmdate('c');
            $updated = $post;
            break;
        }
    }

    if (!$updated) {
        jsonResponse(['error' => 'Post not found'], 404);
    }

    writeJsonFile('posts.json', $posts);
    jsonResponse($updated);
}

if ($method === 'DELETE') {
    $user = requireRole(['admin', 'subadmin']);
    $id = (string)($_GET['id'] ?? '');
    if ($id === '') {
        jsonResponse(['error' => 'Missing id'], 422);
    }

    $posts = readJsonFile('posts.json');
    $before = count($posts);
    $posts = array_values(array_filter($posts, function ($p) use ($id, $user) {
        if (($p['id'] ?? '') !== $id) {
            return true;
        }
        if ($user['role'] === 'admin') {
            return false;
        }
        return ($p['authorId'] ?? '') !== $user['id'];
    }));

    if (count($posts) === $before) {
        jsonResponse(['error' => 'Post not found or not allowed'], 404);
    }

    writeJsonFile('posts.json', $posts);
    jsonResponse(['ok' => true]);
}

jsonResponse(['error' => 'Method not allowed'], 405);
