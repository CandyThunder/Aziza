const editor = document.getElementById('editor');
const contentField = document.getElementById('content-field');
const postForm = document.getElementById('admin-post-form');
const galleryForm = document.getElementById('gallery-form');
const subadminForm = document.getElementById('subadmin-form');

document.querySelectorAll('[data-cmd]').forEach((btn) => {
  btn.addEventListener('click', () => {
    document.execCommand(btn.dataset.cmd, false);
    editor.focus();
  });
});

postForm?.addEventListener('submit', async (e) => {
  e.preventDefault();
  contentField.value = editor.innerHTML;
  const fd = new FormData(postForm);
  const res = await fetch('api/posts.php', { method: 'POST', body: fd });
  const data = await res.json();
  if (!res.ok) return alert(data.error || 'Failed to post');
  alert('Blog published');
  postForm.reset();
  editor.innerHTML = '<p>Write the story...</p>';
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
