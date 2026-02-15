<?php require_once __DIR__ . '/api/lib.php'; $u = currentUser(); if (!$u || !canPostRole($u['role'])) { header('Location: index.php'); exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Desk</title>
  <link rel="stylesheet" href="styles.css"/>
</head>
<body class="admin-body">
  <main class="admin-layout">
    <section>
      <h1>Blog Composer</h1>
      <p>Rich text, image upload, category tags, and publish.</p>
      <form id="admin-post-form" enctype="multipart/form-data" class="stack">
        <input name="title" placeholder="Title" required />
        <input name="category" placeholder="Category" required />
        <input name="cover" placeholder="Cover image URL (optional)" />
        <input name="coverFile" type="file" accept="image/*" />
        <div class="toolbar">
          <button type="button" data-cmd="bold" class="chip">Bold</button>
          <button type="button" data-cmd="italic" class="chip">Italic</button>
          <button type="button" data-cmd="insertUnorderedList" class="chip">List</button>
        </div>
        <div id="editor" class="editor" contenteditable="true"><p>Write the story...</p></div>
        <input type="hidden" name="content" id="content-field" />
        <button class="btn">Publish Blog</button>
      </form>
    </section>

    <section>
      <h2>Gallery Publisher</h2>
      <form id="gallery-form" enctype="multipart/form-data" class="stack">
        <input name="title" placeholder="Artwork title" required />
        <input name="image" placeholder="Image URL (optional)" />
        <input type="file" name="imageFile" accept="image/*" />
        <button class="btn">Add to Gallery</button>
      </form>

      <?php if ($u['role'] === 'admin'): ?>
      <h2>Create Sub Admin</h2>
      <form id="subadmin-form" class="stack">
        <input name="name" placeholder="Name" required />
        <input name="email" type="email" placeholder="Email" required />
        <input name="password" type="password" placeholder="Temporary password" required />
        <button class="btn">Create sub admin</button>
      </form>
      <?php endif; ?>

      <a href="index.php" class="chip">Back to site</a>
    </section>
  </main>
  <script src="admin.js"></script>
</body>
</html>
