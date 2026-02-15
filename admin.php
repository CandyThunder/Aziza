<?php require_once __DIR__ . '/api/lib.php'; $u = currentUser(); if (!$u || !canPostRole($u['role'])) { header('Location: index.php'); exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Studio Publishing Desk</title>
  <link rel="icon" type="image/svg+xml" href="favicon.svg" />
  <link rel="stylesheet" href="styles.css"/>
</head>
<body class="admin-body">
  <div class="admin-shell">
    <aside class="admin-sidebar">
      <h2>Publishing Desk</h2>
      <p>For <?php echo htmlspecialchars($u['name']); ?> (<?php echo htmlspecialchars($u['role']); ?>)</p>
      <nav>
        <a href="#composer" class="chip">Composer</a>
        <a href="#manage" class="chip">Manage Posts</a>
        <a href="#media" class="chip">Gallery</a>
        <?php if ($u['role'] === 'admin'): ?><a href="#team" class="chip">Sub Admins</a><?php endif; ?>
      </nav>
      <a href="index.php" class="chip">Back to Studio</a>
    </aside>

    <main class="admin-main">
      <section id="composer" class="admin-card">
        <header><h1>Rich Blog Composer</h1><p>Compose structured posts with headings, links, lists, quotes, and inline media placement.</p></header>
        <form id="admin-post-form" enctype="multipart/form-data" class="stack">
          <input name="id" id="post-id" type="hidden" />
          <div class="row two">
            <input name="title" id="post-title" placeholder="Post title" required />
            <input name="category" id="post-category" placeholder="Category" required />
          </div>
          <div class="row two">
            <input name="cover" id="post-cover" placeholder="Cover image URL (optional)" />
            <input name="coverFile" id="post-cover-file" type="file" accept="image/*" />
          </div>

          <div class="toolbar">
            <button type="button" data-cmd="formatBlock" data-val="H1" class="chip">H1</button>
            <button type="button" data-cmd="formatBlock" data-val="H2" class="chip">H2</button>
            <button type="button" data-cmd="formatBlock" data-val="P" class="chip">Paragraph</button>
            <button type="button" data-cmd="bold" class="chip">Bold</button>
            <button type="button" data-cmd="italic" class="chip">Italic</button>
            <button type="button" data-cmd="insertUnorderedList" class="chip">List</button>
            <button type="button" data-cmd="formatBlock" data-val="BLOCKQUOTE" class="chip">Quote</button>
            <button type="button" id="insert-link" class="chip">Insert Link</button>
            <button type="button" id="insert-image" class="chip">Insert Inline Image</button>
            <input type="file" id="inline-image-file" accept="image/*" class="hidden" />
          </div>

          <div id="editor" class="editor" contenteditable="true"><h2>Write the story...</h2><p>Use headings and sections to improve readability.</p></div>
          <input type="hidden" name="content" id="content-field" />
          <div class="row">
            <button class="btn" id="publish-btn">Publish Blog</button>
            <button type="button" class="chip" id="reset-editor">Reset</button>
          </div>
        </form>
      </section>

      <section id="manage" class="admin-card">
        <header><h2>Manage Existing Posts</h2><p>Edit, update, or remove published posts.</p></header>
        <ul id="admin-post-list" class="manage-list"></ul>
      </section>

      <section id="media" class="admin-card">
        <header><h2>Gallery Publisher</h2><p>Add standalone artwork images to the public gallery feed.</p></header>
        <form id="gallery-form" enctype="multipart/form-data" class="stack">
          <input name="title" placeholder="Artwork title" required />
          <input name="image" placeholder="Image URL (optional)" />
          <input type="file" name="imageFile" accept="image/*" />
          <button class="btn">Add to Gallery</button>
        </form>
      </section>

      <?php if ($u['role'] === 'admin'): ?>
      <section id="team" class="admin-card">
        <header><h2>Sub Admin Management</h2><p>Create sub admin accounts that can also publish content.</p></header>
        <form id="subadmin-form" class="stack">
          <input name="name" placeholder="Name" required />
          <input name="email" type="email" placeholder="Email" required />
          <input name="password" type="password" placeholder="Temporary password" required />
          <button class="btn">Create Sub Admin</button>
        </form>
      </section>
      <?php endif; ?>
    </main>
  </div>

  <script src="admin.js"></script>
</body>
</html>
