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
      <h1>Publishing Desk</h1>
      <p>Compose long-form posts with headings, lists, quotes, links, and inline images positioned at cursor.</p>
      <form id="admin-post-form" enctype="multipart/form-data" class="stack">
        <input name="id" id="post-id" type="hidden" />
        <input name="title" id="post-title" placeholder="Title" required />
        <input name="category" id="post-category" placeholder="Category" required />
        <input name="cover" id="post-cover" placeholder="Cover image URL (optional)" />
        <input name="coverFile" id="post-cover-file" type="file" accept="image/*" />

        <div class="toolbar">
          <button type="button" data-cmd="formatBlock" data-val="H1" class="chip">H1</button>
          <button type="button" data-cmd="formatBlock" data-val="H2" class="chip">H2</button>
          <button type="button" data-cmd="formatBlock" data-val="P" class="chip">P</button>
          <button type="button" data-cmd="bold" class="chip">Bold</button>
          <button type="button" data-cmd="italic" class="chip">Italic</button>
          <button type="button" data-cmd="insertUnorderedList" class="chip">List</button>
          <button type="button" data-cmd="formatBlock" data-val="BLOCKQUOTE" class="chip">Quote</button>
          <button type="button" id="insert-link" class="chip">Link</button>
          <button type="button" id="insert-image" class="chip">Inline Image</button>
          <input type="file" id="inline-image-file" accept="image/*" class="hidden" />
        </div>

        <div id="editor" class="editor" contenteditable="true"><h2>Write the story...</h2><p>Start with a section heading.</p></div>
        <input type="hidden" name="content" id="content-field" />
        <div class="row">
          <button class="btn" id="publish-btn">Publish Blog</button>
          <button type="button" class="chip" id="reset-editor">Reset</button>
        </div>
      </form>
    </section>

    <section>
      <h2>Manage Posts</h2>
      <ul id="admin-post-list" class="manage-list"></ul>

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
