<?php
require_once __DIR__ . '/lib.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $items = readJsonFile('gallery.json');
    usort($items, fn($a, $b) => strtotime($b['createdAt'] ?? '') <=> strtotime($a['createdAt'] ?? ''));
    jsonResponse($items);
}

if ($method === 'POST') {
    requireRole(['admin', 'subadmin']);
    $title = trim((string)($_POST['title'] ?? 'Untitled'));
    $image = trim((string)($_POST['image'] ?? ''));

    if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['imageFile']['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $name = uniqid('gallery-', true) . '.' . strtolower($ext);
        $dest = UPLOAD_DIR . '/' . $name;
        if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $dest)) {
            $image = 'uploads/' . $name;
        }
    }

    if ($image === '') {
        jsonResponse(['error' => 'Image URL or file required'], 422);
    }

    $items = readJsonFile('gallery.json');
    $entry = [
        'id' => uniqid('g-', true),
        'title' => $title,
        'image' => $image,
        'createdAt' => gmdate('c')
    ];
    $items[] = $entry;
    writeJsonFile('gallery.json', $items);
    jsonResponse($entry, 201);
}

if ($method === 'DELETE') {
    requireRole(['admin', 'subadmin']);
    $id = (string)($_GET['id'] ?? '');
    if ($id === '') {
        jsonResponse(['error' => 'Missing id'], 422);
    }

    $items = readJsonFile('gallery.json');
    $before = count($items);
    $items = array_values(array_filter($items, fn($item) => ($item['id'] ?? '') !== $id));
    if (count($items) === $before) {
        jsonResponse(['error' => 'Not found'], 404);
    }

    writeJsonFile('gallery.json', $items);
    jsonResponse(['ok' => true]);
}

jsonResponse(['error' => 'Method not allowed'], 405);
