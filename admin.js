const editor = document.getElementById('editor');
const contentField = document.getElementById('content-field');
const postForm = document.getElementById('admin-post-form');
const galleryForm = document.getElementById('gallery-form');
const subadminForm = document.getElementById('subadmin-form');
const postList = document.getElementById('admin-post-list');
const postIdField = document.getElementById('post-id');
const postTitleField = document.getElementById('post-title');
const postCategoryField = document.getElementById('post-category');
const postCoverField = document.getElementById('post-cover');
const publishBtn = document.getElementById('publish-btn');
const resetEditorBtn = document.getElementById('reset-editor');
const inlineImageFileInput = document.getElementById('inline-image-file');

async function getPosts() {
  const res = await fetch('api/posts.php');
  return res.json();
}

async function refreshManagePosts() {
  const posts = await getPosts();
  postList.innerHTML = posts
    .map(
      (post) => `
      <li>
        <div>
          <strong>${post.title}</strong>
          <p>${post.category} • ${new Date(post.createdAt).toLocaleDateString()}</p>
        </div>
        <div class="row">
          <button class="chip edit-post" data-id="${post.id}">Edit</button>
          <button class="chip delete-post" data-id="${post.id}">Delete</button>
        </div>
      </li>
      `,
    )
    .join('');

  postList.querySelectorAll('.edit-post').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const allPosts = await getPosts();
      const post = allPosts.find((entry) => entry.id === btn.dataset.id);
      if (!post) return;
      postIdField.value = post.id;
      postTitleField.value = post.title;
      postCategoryField.value = post.category;
      postCoverField.value = post.cover;
      editor.innerHTML = post.content;
      publishBtn.textContent = 'Update Blog';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

  postList.querySelectorAll('.delete-post').forEach((btn) => {
    btn.addEventListener('click', async () => {
      if (!confirm('Delete this post?')) return;
      const res = await fetch(`api/posts.php?id=${encodeURIComponent(btn.dataset.id)}`, { method: 'DELETE' });
      const data = await res.json();
      if (!res.ok) return alert(data.error || 'Failed to delete');
      await refreshManagePosts();
    });
  });
}

function resetComposer() {
  postForm.reset();
  postIdField.value = '';
  editor.innerHTML = '<h2>Write the story...</h2><p>Start with a section heading.</p>';
  publishBtn.textContent = 'Publish Blog';
}

document.querySelectorAll('[data-cmd]').forEach((btn) => {
  btn.addEventListener('click', () => {
    const command = btn.dataset.cmd;
    const value = btn.dataset.val || null;
    document.execCommand(command, false, value);
    editor.focus();
  });
});

document.getElementById('insert-link')?.addEventListener('click', () => {
  const url = prompt('Enter URL');
  if (!url) return;
  document.execCommand('createLink', false, url);
});

document.getElementById('insert-image')?.addEventListener('click', () => {
  const url = prompt('Enter image URL (or Cancel to upload)');
  if (url) {
    document.execCommand('insertImage', false, url);
    return;
  }
  inlineImageFileInput.click();
});

inlineImageFileInput?.addEventListener('change', () => {
  const file = inlineImageFileInput.files?.[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = () => {
    document.execCommand('insertImage', false, reader.result);
  };
  reader.readAsDataURL(file);
});

resetEditorBtn?.addEventListener('click', resetComposer);

postForm?.addEventListener('submit', async (e) => {
  e.preventDefault();
  contentField.value = editor.innerHTML;

  if (postIdField.value) {
    const payload = {
      id: postIdField.value,
      title: postTitleField.value.trim(),
      category: postCategoryField.value.trim(),
      cover: postCoverField.value.trim(),
      content: contentField.value,
    };

    const updateRes = await fetch('api/posts.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const updateData = await updateRes.json();
    if (!updateRes.ok) return alert(updateData.error || 'Failed to update post');
    alert('Post updated');
    resetComposer();
    await refreshManagePosts();
    return;
  }

  const fd = new FormData(postForm);
  const res = await fetch('api/posts.php', { method: 'POST', body: fd });
  const data = await res.json();
  if (!res.ok) return alert(data.error || 'Failed to post');
  alert('Blog published');
  resetComposer();
  await refreshManagePosts();
});

galleryForm?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(galleryForm);
  const res = await fetch('api/gallery.php', { method: 'POST', body: fd });
  const data = await res.json();
  if (!res.ok) return alert(data.error || 'Failed');
  alert('Gallery image added');
  galleryForm.reset();
});

subadminForm?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(subadminForm);
  const payload = {
    name: fd.get('name'),
    email: fd.get('email'),
    password: fd.get('password'),
  };
  const res = await fetch('api/auth.php?action=invite-subadmin', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  const data = await res.json();
  if (!res.ok) return alert(data.error || 'Could not create sub admin');
  alert('Sub admin created');
  subadminForm.reset();
});

refreshManagePosts();
